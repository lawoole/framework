<?php
namespace Lawoole\Foundation\Console;

use Illuminate\Console\Command;
use Illuminate\Encryption\Encrypter;

class KeyGenerateCommand extends Command
{
    /**
     * 命令名
     *
     * @var string
     */
    protected $name = 'key:generate';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = 'Generate an random key';

    /**
     * 执行命令
     */
    public function handle()
    {
        $key = $this->generateRandomKey();

        $this->comment($key);
    }

    /**
     * 随机生成
     *
     * @return string
     */
    protected function generateRandomKey()
    {
        return 'base64:'.base64_encode(
            Encrypter::generateKey($this->laravel['config']['app.cipher'])
        );
    }
}
