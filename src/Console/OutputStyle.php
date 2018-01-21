<?php
namespace Lawoole\Console;

use Illuminate\Console\OutputStyle as LaravelOutputStyle;
use Illuminate\Contracts\Support\Arrayable;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class OutputStyle extends LaravelOutputStyle
{
    /**
     * 默认是信息级别
     *
     * @var int
     */
    protected $verbosity = OutputInterface::VERBOSITY_NORMAL;

    /**
     * 信息级别映射
     *
     * @var array
     */
    protected $verbosityMap = [
        'v'      => OutputInterface::VERBOSITY_VERBOSE,
        'vv'     => OutputInterface::VERBOSITY_VERY_VERBOSE,
        'vvv'    => OutputInterface::VERBOSITY_DEBUG,
        'quiet'  => OutputInterface::VERBOSITY_QUIET,
        'normal' => OutputInterface::VERBOSITY_NORMAL,
    ];

    /**
     * 显示确认
     *
     * @param string $question
     * @param bool $default
     *
     * @return bool
     */
    public function confirm($question, $default = false)
    {
        return parent::confirm($question, $default);
    }

    /**
     * 显示自动完成输入
     *
     * @param string $question
     * @param array $choices
     * @param string $default
     *
     * @return string
     */
    public function anticipate($question, array $choices, $default = null)
    {
        return $this->askWithCompletion($question, $choices, $default);
    }

    /**
     * 显示自动完成输入
     *
     * @param string $question
     * @param array $choices
     * @param string $default
     *
     * @return string
     */
    public function askWithCompletion($question, array $choices, $default = null)
    {
        $question = new Question($question, $default);

        $question->setAutocompleterValues($choices);

        return $this->askQuestion($question);
    }

    /**
     * 显示密码输入
     *
     * @param string $question
     * @param bool $fallback
     *
     * @return string
     */
    public function secret($question, $fallback = true)
    {
        $question = new Question($question);

        $question->setHidden(true)->setHiddenFallback($fallback);

        return $this->askQuestion($question);
    }

    /**
     * 显示选择
     *
     * @param string $question
     * @param array $choices
     * @param string $default
     * @param mixed $attempts
     * @param bool $multiple
     *
     * @return string
     */
    public function choice($question, array $choices, $default = null, $attempts = null, $multiple = null)
    {
        $question = new ChoiceQuestion($question, $choices, $default);

        $question->setMaxAttempts($attempts)->setMultiselect($multiple);

        return $this->askQuestion($question);
    }

    /**
     * 显示表
     *
     * @param array $headers
     * @param array $rows
     * @param string $tableStyle
     * @param array $columnStyles
     */
    public function styleTable($headers, $rows, $tableStyle = 'default', array $columnStyles = [])
    {
        $table = new Table($this);

        if ($rows instanceof Arrayable) {
            $rows = $rows->toArray();
        }

        $table->setHeaders((array) $headers)->setRows($rows)->setStyle($tableStyle);

        foreach ($columnStyles as $columnIndex => $columnStyle) {
            $table->setColumnStyle($columnIndex, $columnStyle);
        }

        $table->render();
    }

    /**
     * 显示信息
     *
     * @param string $string
     * @param mixed $verbosity
     */
    public function info($string, $verbosity = null)
    {
        $this->line($string, 'info', $verbosity);
    }

    /**
     * 显示备注
     *
     * @param string $string
     * @param mixed $verbosity
     */
    public function comment($string, $verbosity = null)
    {
        $this->line($string, 'comment', $verbosity);
    }

    /**
     * 显示询问
     *
     * @param string $string
     * @param mixed $verbosity
     */
    public function question($string, $verbosity = null)
    {
        $this->line($string, 'question', $verbosity);
    }

    /**
     * 显示警告
     *
     * @param string $string
     * @param mixed $verbosity
     */
    public function warn($string, $verbosity = null)
    {
        if (!$this->getFormatter()->hasStyle('warning')) {
            $style = new OutputFormatterStyle('yellow');

            $this->getFormatter()->setStyle('warning', $style);
        }

        $this->line($string, 'warning', $verbosity);
    }

    /**
     * 显示错误
     *
     * @param string $string
     * @param mixed $verbosity
     */
    public function error($string, $verbosity = null)
    {
        $this->line($string, 'error', $verbosity);
    }

    /**
     * 显示警告框
     *
     * @param string $string
     */
    public function alert($string)
    {
        $this->comment(str_repeat('*', strlen($string) + 12));
        $this->comment('*     '.$string.'     *');
        $this->comment(str_repeat('*', strlen($string) + 12));

        $this->writeln('');
    }

    /**
     * 显示行
     *
     * @param string $string
     * @param string $style
     * @param mixed $verbosity
     */
    public function line($string, $style = null, $verbosity = null)
    {
        $styled = $style ? "<$style>$string</$style>" : $string;

        $this->writeln($styled, $this->parseVerbosity($verbosity));
    }

    /**
     * 分析信息输出级别
     *
     * @param string|int $level
     *
     * @return int
     */
    protected function parseVerbosity($level = null)
    {
        if (isset($this->verbosityMap[$level])) {
            $level = $this->verbosityMap[$level];
        } elseif (!is_int($level)) {
            $level = $this->verbosity;
        }

        return $level;
    }
}
