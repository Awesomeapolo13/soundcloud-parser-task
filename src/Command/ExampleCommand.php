<?php

namespace App\Command;

use App\Parser\ParserInterface;
use DiDom\Document;
use DiDom\Query;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
            $io->note(sprintf('Получен url для парсинга: %s', $url));
        }

        $this->parser
            ->setUrl($url);
        $this->parser
            ->parse();



        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
