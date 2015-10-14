<?php

/**
 * This file is part of Satis.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *     Nils Adermann <naderman@naderman.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Composer\Satis\Builder;

use Symfony\Component\Console\Output\OutputInterface;
use Composer\Package\PackageInterface;

/**
 * Builds the archives of the repository.
 *
 * @author James Hautot <james@rezo.net>
 */
class ArchiveBuilderHelper
{
    /** @var OutputInterface The output Interface. */
    private $output;

    /** @var array The 'archive' part of a configuration file. */
    private $archiveConfig;

    /**
     * Helper Constructor.
     *
     * @param OutputInterface $output        The output Interface.
     * @param array           $archiveConfig The 'archive' part of a configuration file.
     */
    public function __construct(OutputInterface $output, array $archiveConfig)
    {
        $this->output = $output;
        $this->archiveConfig = $archiveConfig;
        $this->archiveConfig['skip-dev'] = isset($archiveConfig['skip-dev']) ? (bool) $archiveConfig['skip-dev'] : false;
        $this->archiveConfig['whitelist'] = isset($archiveConfig['whitelist']) ? (array) $archiveConfig['whitelist'] : array();
        $this->archiveConfig['blacklist'] = isset($archiveConfig['blacklist']) ? (array) $archiveConfig['blacklist'] : array();
    }

    /**
     * Gets the directory where to dump archives.
     *
     * @param  string $outputDir The directory where to build
     *
     * @return string $directory The directory where to dump archives
     */
    public function getDirectory($outputDir)
    {
        if (isset($this->archiveConfig['absolute-directory'])) {
            $directory = $this->archiveConfig['absolute-directory'];
        } else {
            $directory = sprintf('%s/%s', $outputDir, $this->archiveConfig['directory']);
        }

        return $directory;
    }

    /**
     * Tells if a package has to be dumped.
     *
     * @param  PackageInterface $package The package to be dumped
     *
     * @return bool false if the package has to be dumped.
     */
    public function skipDump(PackageInterface $package)
    {
        if ('metapackage' === $package->getType()) {
            return true;
        }

        $name = $package->getName();

        if (true === $this->archiveConfig['skip-dev'] && true === $package->isDev()) {
            $this->output->writeln(sprintf("<info>Skipping '%s' (is dev)</info>", $name));
            return true;
        }

        $names = $package->getNames();
        if ($this->archiveConfig['whitelist'] && !array_intersect($this->archiveConfig['whitelist'], $names)) {
            $this->output->writeln(sprintf("<info>Skipping '%s' (is not in whitelist)</info>", $name));
            return true;
        }

        if ($this->archiveConfig['blacklist'] && array_intersect($this->archiveConfig['blacklist'], $names)) {
            $this->output->writeln(sprintf("<info>Skipping '%s' (is in blacklist)</info>", $name));
            return true;
        }

        return false;
    }
}
