<?php
/**
 * Copyright © 2015, Ambroise Maupate
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @file RemoveOrphanContainersCommand.php
 * @author Ambroise Maupate
 */
namespace AM\Bundle\DockerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 *
 */
class RemoveOrphanContainersCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('docker:remove-orphans')
            ->setDescription('Remove database container references if their Docker containers do not exist anymore.')
            ->addOption(
                'force',
                null,
                InputOption::VALUE_NONE,
                'Make changes on database.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $text = "";
        $em = $this->getContainer()->get('doctrine')->getManager();
        $docker = $this->getContainer()->get('docker');
        $manager = $docker->getContainerManager();

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('<question>Do you want to remove every orphan containers references?</question> [y|N] ', false);

        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        $containerEntities = $em->getRepository('AM\Bundle\DockerBundle\Entity\Container')
            ->findBy([]);

        $forceCommand = (boolean) $input->getOption('force');

        // create a new progress bar (50 units)
        $progress = new ProgressBar($output, count($containerEntities));
        $progress->start();
        $text .= PHP_EOL;
        foreach ($containerEntities as $key => $containerEntity) {
            try {
                $realContainer = $manager->find($containerEntity->getContainerId());
                $text .= $realContainer->getId() . " : <info>" . $realContainer->getName() . "</info>" . PHP_EOL;
            } catch (\Http\Client\Plugin\Exception\ClientErrorException $e) {
                $text .= $containerEntity->getContainerId() . " : <error>Does not exist anymore.</error>" . PHP_EOL;
                if ($forceCommand) {
                    $em->remove($containerEntity);
                    $em->flush();
                    $text .= "---> " . $containerEntity->getContainerId() . " : <info>Reference has been deleted.</info>" . PHP_EOL;
                }
            }

            $progress->advance();
        }

        $progress->finish();
        $output->writeln($text);
    }
}
