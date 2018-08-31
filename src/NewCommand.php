<?php
namespace Khunpra\Installer\Console;

use ZipArchive;
use RuntimeException;
use GuzzleHttp\Client;
use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;


class NewCommand extends Command
{
    protected $logo = "
    🍔 🍟 🍕 🍔 🍟 🍕 🍔 🍟 🍕 🍔 🍟 🍕 🍔 🍟 🍕 🍔 🍟 🍕 🍔 🍟 🍕 🍔 🍟 🍕 🍔 🍟 🍕 🍔 🍟 🍕 🍔 🍟 🍕 🍔 🍟 🍕 🍔 🍟 🍔 🍟 🍕 🍔 🍟 🍕 🍔 🍟 🍕 
   
                                                                               █ ▄▀█  █▀▄ █
                                                                              ▐▌          ▐▌
                                                                              █▌▀▄  ▄▄  ▄▀▐█
                                                                             ██▌  ▀▀  ▀▀  ██▌
                                                                            ▄████▄  ▐▌  ▄████▄

    ██╗  ██╗ ██╗  ██╗ ██╗   ██╗ ███╗   ██╗ ██████╗  ██████╗   █████╗      ██████╗ ██╗      ██╗
    ██║ ██╔╝ ██║  ██║ ██║   ██║ ████╗  ██║ ██╔══██╗ ██╔══██╗ ██╔══██╗    ██╔════╝ ██║      ██║
    █████╔╝  ███████║ ██║   ██║ ██╔██╗ ██║ ██████╔╝ ██████╔╝ ███████║    ██║      ██║      ██║
    ██╔═██╗  ██╔══██║ ██║   ██║ ██║╚██╗██║ ██╔═══╝  ██╔══██╗ ██╔══██║    ██║      ██║      ██║
    ██║  ██╗ ██║  ██║ ╚██████╔╝ ██║ ╚████║ ██║      ██║  ██║ ██║  ██║    ╚██████╗ ███████╗ ██║
    ╚═╝  ╚═╝ ╚═╝  ╚═╝  ╚═════╝  ╚═╝  ╚═══╝ ╚═╝      ╚═╝  ╚═╝ ╚═╝  ╚═╝     ╚═════╝ ╚══════╝ ╚═╝
                                                                            by liam3&P0p@mis

    🍔 🍟 🍕 🍔 🍟 🍕 🍔 🍟 🍕 🍔 🍟 🍕 🍔 🍟 🍕 🍔 🍟 🍕 🍔 🍟 🍕 🍔 🍟 🍕 🍔 🍟 🍕 🍔 🍟 🍕 🍔 🍟 🍕 🍔 🍟 🍕 🍔 🍟 🍕 🍔 🍟 🍕 🍔 🍟 🍕 🍔 🍟 🍕 
    ";

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('new')
            ->setDescription('Create a new MIS Workflow application')
            ->addArgument('name', InputArgument::OPTIONAL, 'What the workflow name do you want?')
            ->addOption('dev', null, InputOption::VALUE_NONE, 'Installs the latest "development" release by liam3');
    }
    /**
     * Execute the command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $wfName = $input->getArgument('name');

        if (! class_exists('ZipArchive')) {
            throw new RuntimeException('The Zip PHP extension is not installed. Please install it and try again.');
        }

        if(!$wfName){
            throw new RuntimeException('Please enter your workflow name after command new ex. `khunpra new FixAsset`');
        }

        $directory = ($wfName) ? getcwd().'/'.$wfName : getcwd();

        $this->verifyApplicationDoesntExist($directory);

        $output->writeln('<info>Crafting MIS Workflow Application ...</info>');

        $version = $this->getVersion($input);

        $this->download($zipFile = $this->makeFilename(), $version)
             ->extract($zipFile, $directory)
             ->cleanUp($zipFile);

        $composer = $this->findComposer();
        $output->writeln('<comment> Composer is here : ' . $composer . '</comment>');

        $commands = [
            $composer.' install --no-scripts',
        ];

        $process = new Process(implode(' && ', $commands), $directory, null, null, null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            $process->setTty(true);
        }

        $process->run(function ($type, $line) use ($output) {
            $output->write($line);
        });

        $output->writeln('<comment>' . $this->logo . '</comment>');
        $output->writeln('<info>                                                                                 </info>');
        $output->writeln('<info>    ┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓    </info>');
        $output->writeln('<info>    ┃                                                                       ┃    </info>');
        $output->writeln('<info>    ┃   Success!!!!, MIS Workflow ready! Enjoy to build something amazing   ┃    </info>');
        $output->writeln('<info>    ┃                                                                       ┃    </info>');
        $output->writeln('<info>    ┃   [ run below command to test ]                                       ┃    </info>');
        $output->writeln('<info>    ┃                                                                       ┃    </info>');
        $output->writeln('<info>    ┃   cd </info>' . $wfName . '<info> && cp .env.example .env && php -S localhost:1234          ┃    </info>');
        $output->writeln('<info>    ┃                                                                       ┃    </info>');
        $output->writeln('<info>    ┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛    </info>');
        $output->writeln('<info>                                                                                 </info>');
        $output->writeln('<info>                                                                                 </info>');

    }
    /**
     * Verify that the application does not already exist.
     *
     * @param  string  $directory
     * @return void
     */
    protected function verifyApplicationDoesntExist($directory)
    {
        if ((is_dir($directory) || is_file($directory)) && $directory != getcwd()) {
            throw new RuntimeException('Application already exists!');
        }
    }
    /**
     * Generate a random temporary filename.
     *
     * @return string
     */
    protected function makeFilename()
    {
        return getcwd().'/khuapraWF_Byliam3_'.md5(time().uniqid()).'.zip';
    }
    /**
     * Download the temporary Zip to the given file.
     *
     * @param  string  $zipFile
     * @param  string  $version
     * @return $this
     */
    protected function download($zipFile, $version = 'master')
    {
        switch ($version) {
            case 'develop':
                $filename = 'latest-develop.zip';
                break;
            case 'master':
                $filename = 'latest.zip';
                break;
        }
        $response = (new Client)->get('http://mis_test.metrosystems.co.th/khunpra/' . $filename);
        file_put_contents($zipFile, $response->getBody());
        return $this;
    }
    /**
     * Extract the Zip file into the given directory.
     *
     * @param  string  $zipFile
     * @param  string  $directory
     * @return $this
     */
    protected function extract($zipFile, $directory)
    {
        $archive = new ZipArchive;
        $archive->open($zipFile);
        $archive->extractTo($directory);
        $archive->close();
        return $this;
    }
    /**
     * Clean-up the Zip file.
     *
     * @param  string  $zipFile
     * @return $this
     */
    protected function cleanUp($zipFile)
    {
        @chmod($zipFile, 0777);
        @unlink($zipFile);
        return $this;
    }
    /**
     * Make sure the storage and bootstrap cache directories are writable.
     *
     * @param  string  $appDirectory
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return $this
     */
    protected function prepareWritableDirectories($appDirectory, OutputInterface $output)
    {
        
    }
    /**
     * Get the version that should be downloaded.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @return string
     */
    protected function getVersion(InputInterface $input)
    {
        if ($input->getOption('dev')) {
            return 'develop';
        }
        return 'master';
    }
    /**
     * Get the composer command for the environment.
     *
     * @return string
     */
    protected function findComposer()
    {
        if (file_exists(getcwd().'/composer.phar')) {
            return '"'. PHP_BINARY .'" composer.phar';
        }
        return 'composer';
    }
}