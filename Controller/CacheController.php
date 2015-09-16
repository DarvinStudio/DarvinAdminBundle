<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cache controller
 */
class CacheController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function clearAction()
    {
        $input = new ArrayInput(array());
        $output = new NullOutput();

        return new Response($this->getCachesClearCommand()->run($input, $output));
    }

    /**
     * @return \Darvin\AdminBundle\Command\CachesClearCommand
     */
    private function getCachesClearCommand()
    {
        return $this->get('darvin_admin.cache.clear_command');
    }
}
