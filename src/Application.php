<?php
namespace Lawoole;

use Illuminate\Container\Container;
use Illuminate\Events\EventServiceProvider;
use Illuminate\Support\Str;
use Lawoole\Contracts\Foundation\ApplicationInterface;
use Lawoole\Log\LogServiceProvider;

class Application extends Container implements ApplicationInterface
{
    /**
     * 框架版本号
     */
    const VERSION = '0.1.7';

    /**
     * 项目基础路径
     *
     * @var string
     */
    protected $basePath;

    /**
     * 初始化标记
     *
     * @var bool
     */
    protected $bootstrapped = false;

    /**
     * 启动标记
     *
     * @var bool
     */
    protected $booted = false;

    /**
     * 启动前回调集合
     *
     * @var array
     */
    protected $bootingCallbacks = [];

    /**
     * 启动完成回调集合
     *
     * @var array
     */
    protected $bootedCallbacks = [];

    /**
     * 结束前回调集合
     *
     * @var array
     */
    protected $terminatingCallbacks = [];

    /**
     * 已注册的服务提供者集合
     *
     * @var array
     */
    protected $serviceProviders = [];

    /**
     * 创建服务容器
     *
     * @param string $basePath
     */
    public function __construct($basePath)
    {
        $this->setBasePath($basePath);

        $this->bootstrapContainer();
        $this->registerBaseServiceProviders();

        $this->registerDefaultBindings();
    }

    /**
     * 获得应用名
     *
     * @return string
     */
    public function name()
    {
        return 'The Lawoole framework';
    }

    /**
     * 获得应用版本信息
     *
     * @return string
     */
    public function version()
    {
        return static::VERSION.' (Laravel Components 5.5.*)';
    }

    /**
     * 初始化容器
     */
    protected function bootstrapContainer()
    {
        static::setInstance($this);

        $this->instance('app', $this);

        $this->registerCoreAliases();
    }

    /**
     * 绑定默认实体
     */
    protected function registerDefaultBindings()
    {
        // 异常处理器
        $this->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            \Lawoole\Exceptions\Handler::class
        );

        // Http 处理核心
        $this->singleton(
            \Illuminate\Contracts\Http\Kernel::class,
            \Lawoole\Foundation\Http\Kernel::class
        );

        // Console 处理核心
        $this->singleton(
            \Illuminate\Contracts\Console\Kernel::class,
            \Lawoole\Foundation\Console\Kernel::class
        );
    }

    /**
     * 注册基础服务提供者
     */
    protected function registerBaseServiceProviders()
    {
        $this->register(EventServiceProvider::class);

        $this->register(LogServiceProvider::class);
    }

    /**
     * 判断是否已经初始化
     *
     * @return bool
     */
    public function hasBeenBootstrapped()
    {
        return $this->bootstrapped;
    }

    /**
     * 通过给定的初始化过程，对应用进行初始化
     *
     * @param array $bootstrappers
     */
    public function bootstrapWith(array $bootstrappers)
    {
        // 判断应用是否已经初始化，如果初始化过，就不能够再进行初始化
        if ($this->bootstrapped) {
            return;
        }

        $this->bootstrapped = true;

        foreach ($bootstrappers as $bootstrapper) {
            // 触发初始化过程执行前事件
            $this['events']->fire('bootstrapping: '.$bootstrapper, [$this]);

            // 执行初始化过程
            $this->make($bootstrapper)->bootstrap($this);

            // 触发初始化过程执行后事件
            $this['events']->fire('bootstrapped: '.$bootstrapper, [$this]);
        }
    }

    /**
     * 注册初始化过程执行前事件监听
     *
     * @param string $bootstrapper
     * @param mixed $callback
     */
    public function beforeBootstrapping($bootstrapper, $callback)
    {
        $this['events']->listen('bootstrapping: '.$bootstrapper, $callback);
    }

    /**
     * 注册初始化过程执行后事件监听
     *
     * @param string $bootstrapper
     * @param mixed $callback
     */
    public function afterBootstrapping($bootstrapper, $callback)
    {
        $this['events']->listen('bootstrapped: '.$bootstrapper, $callback);
    }

    /**
     * 设置程序基础路径
     *
     * @param string $basePath
     *
     * @return $this
     */
    public function setBasePath($basePath)
    {
        $this->basePath = rtrim($basePath, '\/');

        $this->bindPathsInContainer();

        return $this;
    }

    /**
     * 获得项目基础路径
     *
     * @param string $path
     *
     * @return string
     */
    public function basePath($path = '')
    {
        return $this->basePath.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * 获得初始化文件路径
     *
     * @param string $path
     *
     * @return string
     */
    public function bootstrapPath($path = '')
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'bootstrap'.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * 获得配置文件路径
     *
     * @param string $path
     *
     * @return string
     */
    public function configPath($path = '')
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'config'.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * 获得数据库文件路径
     *
     * @param string $path
     *
     * @return string
     */
    public function databasePath($path = '')
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'database'.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * 获得公共资源文件路径
     *
     * @param string $path
     *
     * @return string
     */
    public function publicPath($path = '')
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'public'.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * 获得路由规则存储路径
     *
     * @param string $path
     *
     * @return string
     */
    public function routePath($path = '')
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'routes'.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * 获得定时任务存储路径
     *
     * @param string $path
     *
     * @return string
     */
    public function schedulePath($path = '')
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'schedules'.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * 获得数据存储路径
     *
     * @param string $path
     *
     * @return string
     */
    public function storagePath($path = '')
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'storage'.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * 获得资源文件路径
     *
     * @param string $path
     *
     * @return string
     */
    public function resourcePath($path = '')
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'resources'.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * 获得语言文件路径
     *
     * @param string $path
     *
     * @return string
     */
    public function langPath($path = '')
    {
        return $this->resourcePath('lang').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * 绑定程序路径到容器中
     */
    protected function bindPathsInContainer()
    {
        $this->instance('path.base', $this->basePath());
        $this->instance('path.bootstrap', $this->bootstrapPath());
        $this->instance('path.config', $this->configPath());
        $this->instance('path.database', $this->databasePath());
        $this->instance('path.lang', $this->langPath());
        $this->instance('path.public', $this->publicPath());
        $this->instance('path.resource', $this->resourcePath());
        $this->instance('path.route', $this->routePath());
        $this->instance('path.storage', $this->storagePath());
    }

    /**
     * 获得或检查当前运行环境
     *
     * @return string
     */
    public function environment()
    {
        if ($this->bound('config')) {
            $environment = $this->make('config')->get('app.environment', 'production');
        } else {
            $environment = 'production';
        }

        if (func_num_args() > 0) {
            $patterns = is_array(func_get_arg(0)) ? func_get_arg(0) : func_num_args();

            foreach ($patterns as $pattern) {
                if (Str::is($pattern, $environment)) {
                    return true;
                }
            }

            return false;
        }

        return $environment;
    }

    /**
     * 判断是否运行在控制台中
     *
     * @return bool
     */
    public function runningInConsole()
    {
        return php_sapi_name() == 'cli' || php_sapi_name() == 'phpdbg';
    }

    /**
     * 判断是否运行在测试环境中
     *
     * @return bool
     */
    public function runningInTesting()
    {
        return $this->environment() == 'test';
    }

    /**
     * 判断程序是否处于维护状态
     *
     * @return bool
     */
    public function isDownForMaintenance()
    {
        return file_exists($this->storagePath('framework/down'));
    }

    /**
     * 注册所有已配置的服务提供者
     */
    public function registerConfiguredProviders()
    {
        $providers = $this['config']->get('app.providers', []);

        foreach ($providers as $provider) {
            $this->register($provider);
        }
    }

    /**
     * 注册服务提供者
     *
     * @param \Illuminate\Support\ServiceProvider|string $provider
     * @param array $options
     * @param bool $force
     *
     * @return \Illuminate\Support\ServiceProvider
     */
    public function register($provider, $options = [], $force = false)
    {
        if (($registered = $this->getProvider($provider)) && !$force) {
            // 如果服务提供者以及注册，且未设置为强制注册，则直接返回老服务提供者
            // 对象，不再进行注册
            return $registered;
        }

        // 如果只提供了服务提供者的类名，则实例化服务提供者
        if (is_string($provider)) {
            $provider = new $provider($this);
        }

        if (method_exists($provider, 'register')) {
            // 运行服务提供者的注册方法
            $provider->register();
        }

        $this->markAsRegistered($provider);

        // 如果应用已经启动，则直接启动新注册的服务提供者
        if ($this->booted) {
            $this->bootProvider($provider);
        }

        return $provider;
    }

    /**
     * 注册延迟加载的服务提供者
     *
     * @param string $provider
     * @param string|null $service
     */
    public function registerDeferredProvider($provider, $service = null)
    {
        // 框架主要针对长期处理设计，不考虑服务加载带来的损耗，不做服务提供者延迟
        // 加载的处理
        $this->register($provider);
    }

    /**
     * 标记服务提供者为已注册
     *
     * @param \Illuminate\Support\ServiceProvider $provider
     */
    protected function markAsRegistered($provider)
    {
        $this->serviceProviders[] = $provider;
    }

    /**
     * 从已注册的服务提供者集合中获得指定的服务提供者
     *
     * @param \Illuminate\Support\ServiceProvider|string $provider
     *
     * @return \Illuminate\Support\ServiceProvider|null
     */
    public function getProvider($provider)
    {
        $name = is_string($provider) ? $provider : get_class($provider);

        foreach ($this->serviceProviders as $provider) {
            if ($provider instanceof $name) {
                return $provider;
            }
        }

        return null;
    }

    /**
     * 判断应用是否已经启动
     *
     * @return bool
     */
    public function isBooted()
    {
        return $this->booted;
    }

    /**
     * 启动应用和其中的服务提供者
     */
    public function boot()
    {
        if ($this->booted) {
            return;
        }

        $this->fireCallbacksWithApp($this->bootingCallbacks);

        // 逐一启动服务提供者
        array_walk($this->serviceProviders, function ($provider) {
            $this->bootProvider($provider);
        });

        $this->booted = true;

        $this->fireCallbacksWithApp($this->bootedCallbacks);
    }

    /**
     * 启动服务提供者
     *
     * @param \Illuminate\Support\ServiceProvider $provider
     *
     * @return mixed
     */
    protected function bootProvider($provider)
    {
        if (method_exists($provider, 'boot')) {
            return $this->call([$provider, 'boot']);
        }

        return null;
    }

    /**
     * 注册服务提供者启动前回调
     *
     * @param callable $callback
     */
    public function booting($callback)
    {
        $this->bootingCallbacks[] = $callback;

        // 如果应用已经启动，则直接回调这一函数
        if ($this->booted) {
            $this->fireCallbacksWithApp([$callback]);
        }
    }

    /**
     * 注册服务提供者启动完成回调
     *
     * @param callable $callback
     */
    public function booted($callback)
    {
        $this->bootedCallbacks[] = $callback;
    }

    /**
     * 注册应用停止回调
     *
     * @param callable $callback
     */
    public function terminating($callback)
    {
        $this->terminatingCallbacks[] = $callback;
    }

    /**
     * 停止应用
     */
    public function terminate()
    {
        $this->fireCallbacksWithApp($this->terminatingCallbacks);
    }

    /**
     * 获得当前配置的地区
     *
     * @return string
     */
    public function getLocale()
    {
        return $this['config']['app.locale'];
    }

    /**
     * 设置当前地区
     *
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this['config']->set('app.locale', $locale);

        $this['translator']->setLocale($locale);

        $this['events']->dispatch(new Events\LocaleUpdated($locale));
    }

    /**
     * 判断当前配置的地区是否为给出的地区
     *
     * @param string $locale
     * @return bool
     */
    public function isLocale($locale)
    {
        return $this->getLocale() == $locale;
    }

    /**
     * 逐一执行回调
     *
     * @param array $callbacks
     */
    protected function fireCallbacksWithApp(array $callbacks)
    {
        foreach ($callbacks as $callback) {
            call_user_func($callback, $this);
        }
    }

    /**
     * 获得服务提供者缓存文件路径
     *
     * @return string
     */
    public function getCachedServicesPath()
    {
        return $this->bootstrapPath('cache/services.php');
    }

    /**
     * 获得包缓存文件路径
     *
     * @return string
     */
    public function getCachedPackagesPath()
    {
        return $this->bootstrapPath('cache/packages.php');
    }

    /**
     * 注册核心别名
     */
    public function registerCoreAliases()
    {
        $aliases = [
            \Lawoole\Application::class                               => 'app',
            \Lawoole\Contracts\Foundation\ApplicationInterface::class => 'app',
            \Illuminate\Container\Container::class                    => 'app',
            \Illuminate\Contracts\Container\Container::class          => 'app',
            \Illuminate\Contracts\Foundation\Application::class       => 'app',
            \Illuminate\View\Compilers\BladeCompiler::class           => 'blade.compiler',
            \Illuminate\Contracts\Cache\Factory::class                => 'cache',
            \Illuminate\Contracts\Cache\Repository::class             => 'cache',
            \Illuminate\Cache\Repository::class                       => 'cache.store',
            \Illuminate\Contracts\Cache\Repository::class             => 'cache.store',
            \Illuminate\Contracts\Config\Repository::class            => 'config',
            \Illuminate\Support\Composer::class                       => 'composer',
            \Illuminate\Database\ConnectionResolverInterface::class   => 'db',
            \Illuminate\Database\DatabaseManager::class               => 'db',
            \Lawoole\Routing\ControllerDispatcher::class              => 'dispatcher',
            \Illuminate\Contracts\Encryption\Encrypter::class         => 'encrypter',
            \Illuminate\Contracts\Events\Dispatcher::class            => 'events',
            \Illuminate\Filesystem\Filesystem::class                  => 'files',
            \Illuminate\Filesystem\FilesystemManager::class           => 'filesystem',
            \Illuminate\Contracts\Filesystem\Factory::class           => 'filesystem',
            \Illuminate\Contracts\Filesystem\Filesystem::class        => 'filesystem.disk',
            \Illuminate\Contracts\Filesystem\Cloud::class             => 'filesystem.cloud',
            \Illuminate\Contracts\Hashing\Hasher::class               => 'hash',
            \Lawoole\Homer\HomerManager::class                        => 'homer',
            \Psr\Log\LoggerInterface::class                           => 'log',
            \Illuminate\Database\Migrations\Migrator::class           => 'migrator',
            \Illuminate\Database\Migrations\MigrationCreator::class   => 'migration.creator',
            \Illuminate\Contracts\Queue\Factory::class                => 'queue',
            \Illuminate\Contracts\Queue\Queue::class                  => 'queue.connections',
            \Symfony\Component\HttpFoundation\Request::class          => 'request',
            \Illuminate\Http\Request::class                           => 'request',
            \Lawoole\Routing\RequestManager::class                    => 'request.manager',
            \Lawoole\Routing\Router::class                            => 'router',
            \Illuminate\Console\Scheduling\Schedule::class            => 'schedule',
            \Lawoole\Swoole\Server::class                             => 'server',
            \Lawoole\Server\ServerManager::class                      => 'server.manager',
            \Swoole\Server::class                                     => 'server.swoole',
            \Illuminate\Validation\Factory::class                     => 'validator',
            \Illuminate\Contracts\Validation\Factory::class           => 'validator',
            \Illuminate\View\Factory::class                           => 'view',
            \Illuminate\Contracts\View\Factory::class                 => 'view',
        ];

        foreach ($aliases as $alias => $abstract) {
            $this->alias($abstract, $alias);
        }
    }
}
