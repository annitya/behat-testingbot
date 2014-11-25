<?php
/**
 * @author Henning Kvinnesland <henning@keyteq.no>
 * @since 17.11.14
 */

namespace ResultSubmitter\TestingBot;

use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Context\Exception\ContextNotFoundException;
use Behat\Behat\EventDispatcher\Event\AfterScenarioTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Mink\Driver\Selenium2Driver;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventHandler implements EventSubscriberInterface
{
    protected $key;
    protected $secret;

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            ScenarioTested::AFTER => array('submitResultHandler', 0)
        );
    }

    public function submitResultHandler(AfterScenarioTested $event)
    {
        try {
            $this->submitEvent($event);
        } catch (ContextNotFoundException $e) {
            return;
        }
    }

    public function submitEvent(AfterScenarioTested $event)
    {
        $context = $event->getEnvironment();
        if (!$context instanceof InitializedContextEnvironment) {
            return;
        }

        $context = $context->getContext('FeatureContext');
        $driver = $context->getSession()->getDriver();
        if (!$driver instanceof Selenium2Driver) {
            return;
        }

        $sessionId = $driver->getWebDriverSessionId();
        $success = $event->getTestResult()->isPassed();

        $options = array(
            'auth' => array($this->key, $this->secret),
            'body' => array(
                'test' => array('success' => (int)$success)
            )
        );

        $client = new Client();
        try {
            $client->put('https://api.testingbot.com/v1/tests/' . $sessionId, $options);
        }
        catch (ClientException $e) {}
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @param string $secret
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
    }
}
