<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module
{
    /**
     * available locales in the application
     */
    const LOCALE_AVAILABLE = 'de_DE,en_US';


    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $this->setLocaleByAcceptedLang($e);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig() {
        return array(
            'factories' => array(
                'Application\Resource\SupportedLocale' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new Resource\SupportedLocale($dbAdapter);
                    return $table;
                },
                'Application\Resource\Translation' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new Resource\Translation($dbAdapter);
                    return $table;
                },
                'Application\Resource\TranslationBase' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new Resource\TranslationBase($dbAdapter);
                    return $table;
                },
                'Application\Resource\TranslationFile' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new Resource\TranslationFile($dbAdapter);
                    return $table;
                },
                'Application\Resource\Suggestion' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new Resource\Suggestion($dbAdapter);
                    return $table;
                },
            ),
        );
    }

    /**
     * define locale by HTTP Header Accept-Language
     *
     * @param MvcEvent $e
     */
    protected function setLocaleByAcceptedLang($e)
    {
        /** @var \Zend\Http\Request $request */
        $request = $e->getRequest();
        $headers = $request->getHeaders();
        if ($headers->has('Accept-Language')) {
            $availableLocales = explode(',', self::LOCALE_AVAILABLE);
            $locales = $headers->get('Accept-Language')->getPrioritized();

            foreach ($locales as $locale) {
                $localeString = $locale->getLanguage();
                if (false === strpos($localeString, '-')) {
                    // de    => de_DE
                    $localeString = $localeString . '_' . strtoupper($localeString);
                } else {
                    // en-US => en_US
                    $localeString = str_replace('-', '_', $localeString);
                }

                if (in_array($localeString, $availableLocales)) {
                    $e->getApplication()
                        ->getServiceManager()
                        ->get('MvcTranslator')
                        ->setLocale($localeString);
                    return;
                }
            }
        }
    }
}
