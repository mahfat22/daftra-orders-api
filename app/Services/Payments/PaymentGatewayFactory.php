<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\PaymentMethod;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class PaymentGatewayFactory
{
    /**
     * @var array<string, PaymentGatewayInterface>
     */
    private array $gateways = [];

    /**
     * @var array<string, array>
     */
    private array $gatewayConfigs;

    /**
     * @var array<string>
     */
    private array $enabledGateways;

    public function __construct()
    {
        $this->gatewayConfigs = config('payments.gateways', []);
        $this->enabledGateways = config('payments.enabled', []);
        $this->registerGateways();
    }

    /**
     * Get a payment gateway by method.
     */
    public function getGateway(PaymentMethod|string $method): PaymentGatewayInterface
    {
        $methodValue = $method instanceof PaymentMethod ? $method->value : $method;

        if (isset($this->gateways[$methodValue])) {
            return $this->gateways[$methodValue];
        }

        if (isset($this->gatewayConfigs[$methodValue])) {
            $config = $this->gatewayConfigs[$methodValue];
            $gatewayClass = $config['class'] ?? null;

            if ($gatewayClass && class_exists($gatewayClass)) {
                try {
                    $gateway = $this->createGatewayInstance($gatewayClass, $config['config'] ?? []);
                    $this->registerGateway($methodValue, $gateway);

                    return $gateway;
                } catch (\Throwable $e) {
                    Log::warning("Failed to create payment gateway '{$methodValue}': ".$e->getMessage());
                }
            }
        }

        throw new InvalidArgumentException("Payment gateway not found for method: {$methodValue}");
    }

    /**
     * Get all available gateway methods.
     *
     * @return array<string>
     */
    public function getAvailableMethods(): array
    {
        return array_keys($this->gateways);
    }

    /**
     * Get gateway information for all enabled gateways.
     *
     * @return array<string, array>
     */
    public function getGatewayInfo(): array
    {
        $info = [];

        foreach ($this->gateways as $method => $gateway) {
            $config = $this->gatewayConfigs[$method] ?? [];
            $info[$method] = [
                'name' => $config['name'] ?? ucfirst($method),
                'description' => $config['description'] ?? '',
                'method' => $method,
                'enabled' => true,
                'features' => $config['features'] ?? [],
                'currencies' => $config['currencies'] ?? ['USD'],
            ];
        }

        return $info;
    }

    /**
     * Get only enabled gateways.
     *
     * @return array<string, array>
     */
    public function getEnabledGateways(): array
    {
        $enabled = [];

        foreach ($this->gatewayConfigs as $method => $config) {
            if ($config['enabled'] ?? false) {
                $enabled[$method] = $config;
            }
        }

        return $enabled;
    }

    /**
     * Check if a gateway method is supported.
     */
    public function isMethodSupported(string $method): bool
    {
        return isset($this->gateways[$method]);
    }

    /**
     * Register a new gateway.
     */
    public function registerGateway(string $method, PaymentGatewayInterface $gateway): void
    {
        $this->gateways[$method] = $gateway;
    }

    /**
     * Register all configured gateways.
     */
    private function registerGateways(): void
    {
        foreach ($this->gatewayConfigs as $method => $config) {
            if (! in_array($method, $this->enabledGateways) || ! ($config['enabled'] ?? true)) {
                continue;
            }

            $gatewayClass = $config['class'] ?? null;
            if (! $gatewayClass || ! class_exists($gatewayClass)) {
                continue;
            }

            try {
                $gateway = $this->createGatewayInstance($gatewayClass, $config['config'] ?? []);
                $this->registerGateway($method, $gateway);
            } catch (\Throwable $e) {
                Log::warning("Failed to register payment gateway '{$method}': ".$e->getMessage());
            }
        }

        if (config('payments.discovery.auto_discover', true)) {
            $this->autoDiscoverGateways();
        }
    }

    /**
     * Create a gateway instance with configuration.
     *
     * @param  array<string, mixed>  $config
     */
    private function createGatewayInstance(string $gatewayClass, array $config = []): PaymentGatewayInterface
    {
        try {
            $reflection = new \ReflectionClass($gatewayClass);
            $constructor = $reflection->getConstructor();

            if ($constructor && $constructor->getNumberOfParameters() > 0) {
                return new $gatewayClass($config);
            }
        } catch (\ReflectionException $e) {
        }

        $gateway = new $gatewayClass;

        if (method_exists($gateway, 'setConfig')) {
            $gateway->setConfig($config);
        }

        return $gateway;
    }

    /**
     * Auto-discover payment gateways in configured paths.
     */
    private function autoDiscoverGateways(): void
    {
        $scanPaths = config('payments.discovery.scan_paths', []);

        foreach ($scanPaths as $path) {
            if (! is_dir($path)) {
                continue;
            }

            $files = glob($path.'/*Gateway.php');

            foreach ($files as $file) {
                $this->discoverGatewayFromFile($file);
            }
        }
    }

    /**
     * Discover and register a gateway from a file.
     */
    private function discoverGatewayFromFile(string $file): void
    {
        $className = pathinfo($file, PATHINFO_FILENAME);
        $namespace = 'App\\Services\\Payments\\Gateways\\';
        $fullClass = $namespace.$className;

        if (! class_exists($fullClass)) {
            return;
        }

        try {
            $reflection = new \ReflectionClass($fullClass);

            if ($reflection->isAbstract() || $reflection->isInterface()) {
                return;
            }

            if (! $reflection->implementsInterface(PaymentGatewayInterface::class)) {
                return;
            }

            $gateway = new $fullClass;
            $method = $gateway->getMethod();

            if (isset($this->gateways[$method])) {
                return;
            }

            $this->registerGateway($method, $gateway);

        } catch (\Throwable $e) {
            Log::debug("Gateway discovery failed for {$fullClass}: ".$e->getMessage());
        }
    }

    /**
     * Get gateway configuration.
     *
     * @param  array<string, mixed>  $default
     * @return array<string, mixed>
     */
    public function getGatewayConfig(string $method, array $default = []): array
    {
        return $this->gatewayConfigs[$method]['config'] ?? $default;
    }

    /**
     * Reload gateway configurations (useful for testing).
     */
    public function reloadGateways(): void
    {
        $this->gateways = [];
        $this->gatewayConfigs = config('payments.gateways', []);
        $this->enabledGateways = config('payments.enabled', []);
        $this->registerGateways();
    }
}
