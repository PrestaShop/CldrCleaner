<?php

namespace PrestaShop\CldrCleaner\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class CleanZipCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('cleanzip')
            ->setDescription('Clean the target CLDR core.zip')
            ->addArgument(
                'path',
                InputArgument::OPTIONAL,
                'Core.zip location'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!file_exists(getcwd().'/iso_list.yml')) {
            $output->writeln("<error>The iso list file does not exists.</error>");
            exit;
        }

        $isoCodes = Yaml::parse(file_get_contents(getcwd().'/iso_list.yml'));

        $path = $input->getArgument('path');
        if (!$path) {
            $path = getcwd();
        } else {
            if (substr($path, 0, 1) !== '/') {
                $path = realpath(getcwd().'/'.$path);
            }
        }

        if (!file_exists($path)) {
            $output->writeln("<error>The cldr core.zip file cannot be found.</error>");
            exit;
        }

        @mkdir('temp');
        $zip = new \ZipArchive();
        if ($zip->open($path)) {
            $zip->extractTo('temp');
            $zip->close();
        }

        if ($handle = opendir(getcwd().'/temp/main')) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry !== '.' && $entry !== '..') {
                    if (!in_array(strtolower($entry), $isoCodes['list'])) {
                        $files = scandir(getcwd().'/temp/main/'.$entry);
                        foreach ($files as $file) {
                            if ($file !== '.' && $file !== '..') {
                                unlink(getcwd().'/temp/main/'.$entry.'/'.$file);
                            }
                        }
                        rmdir(getcwd().'/temp/main/'.$entry);
                    }
                }
            }
            closedir($handle);
        }

        $this->zip(getcwd().'/temp', getcwd().'/core.zip');

        rename(getcwd().'/core.zip', $path);
    }

    function zip($source, $destination)
    {
        $zip = new \ZipArchive();
        if (!$zip->open($destination, \ZipArchive::CREATE)) {
            return false;
        }

        $source = str_replace('\\', '/', realpath($source));

        if (is_dir($source) === true) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($source),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($files as $file) {
                $file = str_replace('\\', '/', $file);

                if (in_array(substr($file, strrpos($file, '/')+1), array('.', '..'))) {
                    continue;
                }

                $file = realpath($file);

                if (is_dir($file) === true) {
                    $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                }
                else if (is_file($file) === true) {
                    $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                }
            }
        }
        else if (is_file($source) === true) {
            $zip->addFromString(basename($source), file_get_contents($source));
        }

        return $zip->close();
    }
}
