<?php declare(strict_types=1);
/**
 * @author    Alexander Volodin <mr-stanlik@yandex.ru>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Clear caches command
 */
class ClearCachesCommand extends Command
{
    /**
     * @var array
     */
    private $fastCaches;

    /**
     * @var array
     */
    private $listCaches;

    /**
     * @var array
     */
    private $cacheIds;

    /**
     * {@inheritdoc}
     */
    public function __construct($name = null, $description = null, array $fastCaches, array $listCaches)
    {
        parent::__construct($name);

        $this->setDescription($description);
        $this->fastCaches = $fastCaches;
        $this->listCaches = $listCaches;
        $this->cacheIds   = [];
    }

    /**
     * @param array $cacheIds Ids of caches
     */
    public function addCacheIds(array $cacheIds)
    {
        $this->cacheIds = $cacheIds;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        if (empty($this->cacheIds)) {
            foreach ($this->fastCaches as $name => $command) {
                $io->comment(sprintf('Running "%s" command...', $name));
                $result = $this->getApplication()->run(new StringInput($command), $output);

                if ($result > 0) {
                    return $result;
                }
            }
        } else {
            foreach ($this->cacheIds as $id) {
                $io->comment(sprintf('Running "%s" command...', $id));
                $result = $this->getApplication()->run(new StringInput($this->listCaches[$id]), $output);

                if ($result > 0) {
                    return $result;
                }
            }
        }

        return 0;
    }
}
