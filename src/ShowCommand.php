<?php namespace Acme;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use GuzzleHttp\Client;


class ShowCommand extends Command {

    private $APIkey;
    private Client $httpClient;
    private $APIurl;

    public function __construct(Client $client){
        parent::__construct();
        $this->httpClient = $client;
        $this->APIkey = $_ENV['OMDB_API_KEY'];
        $this->APIurl = $_ENV['OMDB_API_URL'];
    }

    public function configure()
    {
        $this->setName('show')
             ->setDescription('Show a movie details')
             ->addArgument('title', InputArgument::REQUIRED, 'Movie title to search for.')
             ->addOption('fullPlot', null, InputOption::VALUE_NONE, 'Show full plot of the movie.');
    
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $title = $input->getArgument('title');
        $fullPlot = $input->getOption('fullPlot') ? 'full' : 'short';

        $response = $this->httpClient->request('GET', $this->APIurl, [
            'query' => [
                't' => $title,
                'plot' => $fullPlot,
                'apikey' => $this->APIkey
            ]
        ]);


        $data = json_decode($response->getBody(), true);

        if (isset($data['Error'])) {
            $output->writeln("<error>Movie not found!</error>");
            return Command::FAILURE;
        }

        $rows = [];
        foreach ($data as $label => $key) {
            if (isset($data[$key])) {
                $rows[] = [$label, $data[$key]];
            }
        }

        $table = new Table($output);
        $table->setHeaders(['<info>' . $data['Title'] . ' - ' . $data['Year'] . '</info>'])->setRows($rows);
        $table->render();

    }


}
