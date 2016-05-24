<?php

namespace PrestaShop\CldrCleaner\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Yaml\Yaml;

class GenerateIsoArrayCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('generateisolist')
            ->setDescription('Generate the iso list on a config file')
            ->addArgument(
                'path',
                InputArgument::OPTIONAL,
                'The install folder of PrestaShop'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        set_time_limit(0);

        $path = $input->getArgument('path');
        if (!$path) {
            $path = getcwd();
        } else {
            if (substr($path, 0, 1) !== '/') {
                $path = realpath(getcwd().'/'.$path);
            }
        }

        $output->writeln("");
        $output->writeln("<comment>Searching for langagues on folder: $path</comment>");
        $output->writeln("");

        $languages = array(
            'an-ar',
            'af-za',
            'es-ar',
            'ar-sa',
            'az-az',
            'bg-bg',
            'bn-bd',
            'pt-br',
            'bs-ba',
            'br-fr',
            'ca-es',
            'es-co',
            'cs-cz',
            'da-dk',
            'de-de',
            'el-gr',
            'en-us',
            'es-es',
            'et-ee',
            'eu-es',
            'fa-ir',
            'fi-fi',
            'fo-fo',
            'fr-fr',
            'ga-ie',
            'en-gb',
            'gl-es',
            'he-il',
            'hi-in',
            'hr-hr',
            'hu-hu',
            'hy-am',
            'id-id',
            'it-it',
            'ja-jp',
            'ka-ge',
            'ko-kr',
            'lo-la',
            'lt-lt',
            'lv-lv',
            'mk-mk',
            'ml-in',
            'ms-my',
            'es-mx',
            'nl-nl',
            'no-no',
            'pl-pl',
            'pt-pt',
            'ro-ro',
            'ru-ru',
            'si-lk',
            'sl-si',
            'sk-sk',
            'sq-al',
            'sr-cs',
            'sv-se',
            'sw-ke',
            'ta-in',
            'te-in',
            'th-th',
            'tr-tr',
            'zh-tw',
            'ug-cn',
            'uk-ua',
            'ur-pk',
            'vi-vn',
            'zh-cn',
            'nn-no',
            'fr-ca',
            'de-ch',
            'eo-eo',
        );

        // Search on all base languages

        if ($handle = opendir($path.'/langs')) {
            while (false !== ($entry = readdir($handle))) {
                if (file_exists($path.'/langs/'.$entry.'/language.xml')) {
                    $crawler = new Crawler(file_get_contents($path.'/langs/'.$entry.'/language.xml'));
                    $value = $crawler->filterXPath('//language/language_code')->text();
                    if (!in_array($entry, $languages)) {
                      $languages[] = strtolower($entry);
                    }
                    if (!in_array($value, $languages)) {
                        $languages[] = strtolower($value);
                    }
                }
            }
            closedir($handle);
        }

        // Search on all countries

        if (file_exists($path.'/data/xml/country.xml')) {
            $crawler = new Crawler(file_get_contents($path.'/data/xml/country.xml'));
            $countries = $crawler->filterXPath('//entity_country/entities/country');
            foreach ($countries->extract('iso_code') as $iso) {
                if (!in_array($iso, $languages)) {
                    $languages[] = strtolower($iso);
                }
            }
        }

        $yaml = Yaml::dump(array('list' => $languages));
        file_put_contents(getcwd().'/iso_list.yml', $yaml);

        $output->write(count($languages)." iso(s) extracted.\n");
    }
}
