<?php
namespace Lawoole\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use RuntimeException;

class ViewClearCommand extends Command
{
    /**
     * 命令名
     *
     * @var string
     */
    protected $name = 'view:clear';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = 'Clear all compiled view files';

    /**
     * 文件系统实例
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * 创建清理模板缓存命令实例
     *
     * @param \Illuminate\Filesystem\Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * 执行命令
     */
    public function handle()
    {
        $path = $this->laravel['config']['view.compiled'];

        if (!$path) {
            throw new RuntimeException('View path not found');
        }

        foreach ($this->files->glob("{$path}/*") as $view) {
            $this->files->delete($view);
        }

        $this->info('Compiled views cleared!');
    }
}
