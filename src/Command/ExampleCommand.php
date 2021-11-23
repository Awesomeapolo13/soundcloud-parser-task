<?php

namespace App\Command;

use App\Parser\ParserInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class ExampleCommand
 *
 * Класс команды вызывающей SoundCloudParser
 *
 * @package App\Command
 */
class ExampleCommand extends Command
{
    protected static $defaultName = 'app:parse-example';
    protected static $defaultDescription = 'Парсит html страницу с треками автора с сайта SoundCloud';
    /**
     * @var ParserInterface
     */
    private $parser;

    public function __construct(ParserInterface $parser)
    {
        parent::__construct();

        $this->parser = $parser;
    }


    protected function configure(): void
    {
        $this
            ->addArgument('url', InputArgument::REQUIRED, 'Url адрес SoundCloud с указанием артиста, например https://soundcloud.com/lakeyinspired');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $url = $input->getArgument('url');

        if ($url) {
            $io->text(sprintf('Получен url для парсинга: %s', $url));
        }

        $this->parser->setUrl($url);

        try {
            $this->parser->parse();
        } catch (\Exception $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        $io->success('Команда выполнилась успешно! Уникальный данные авторов и треков сохранены в БД');

        return Command::SUCCESS;
    }
}
