<?php

/**
 * Routes.
 *
 * @copyright Zikula contributors (Zikula)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Zikula contributors <info@ziku.la>.
 * @see https://ziku.la
 * @version Generated by ModuleStudio 1.4.0 (https://modulestudio.de).
 */

declare(strict_types=1);

namespace Zikula\RoutesModule\Base;

use Exception;
use Psr\Log\LoggerInterface;
use Zikula\Core\AbstractExtensionInstaller;
use Zikula\RoutesModule\Entity\RouteEntity;

/**
 * Installer base class.
 */
abstract class AbstractRoutesModuleInstaller extends AbstractExtensionInstaller
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string[]
     */
    protected $entities = [
        RouteEntity::class,
    ];

    public function install(): bool
    {
        // create all tables from according entity definitions
        try {
            $this->schemaTool->create($this->entities);
        } catch (Exception $exception) {
            $this->addFlash('error', $this->__('Doctrine Exception') . ': ' . $exception->getMessage());
            $this->logger->error(
                '{app}: Could not create the database tables during installation. Error details: {errorMessage}.',
                ['app' => 'ZikulaRoutesModule', 'errorMessage' => $exception->getMessage()]
            );
    
            return false;
        }
    
        // set up all our vars with initial values
        $this->setVar('routeEntriesPerPage', 10);
        $this->setVar('showOnlyOwnEntries', false);
        $this->setVar('allowModerationSpecificCreatorForRoute', false);
        $this->setVar('allowModerationSpecificCreationDateForRoute', false);
    
        // initialisation successful
        return true;
    }
    
    public function upgrade(string $oldVersion): bool
    {
    /*
        // upgrade dependent on old version number
        switch ($oldVersion) {
            case '1.0.0':
                // do something
                // ...
                // update the database schema
                try {
                    $this->schemaTool->update($this->entities);
                } catch (Exception $exception) {
                    $this->addFlash('error', $this->__('Doctrine Exception') . ': ' . $exception->getMessage());
                    $this->logger->error(
                        '{app}: Could not update the database tables during the upgrade.'
                            . ' Error details: {errorMessage}.',
                        ['app' => 'ZikulaRoutesModule', 'errorMessage' => $exception->getMessage()]
                    );
    
                    return false;
                }
        }
    */
    
        // update successful
        return true;
    }
    
    public function uninstall(): bool
    {
        try {
            $this->schemaTool->drop($this->entities);
        } catch (Exception $exception) {
            $this->addFlash('error', $this->__('Doctrine Exception') . ': ' . $exception->getMessage());
            $this->logger->error(
                '{app}: Could not remove the database tables during uninstallation. Error details: {errorMessage}.',
                ['app' => 'ZikulaRoutesModule', 'errorMessage' => $exception->getMessage()]
            );
    
            return false;
        }
    
        // remove all module vars
        $this->delVars();
    
        // uninstallation successful
        return true;
    }
    
    /**
     * @required
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
