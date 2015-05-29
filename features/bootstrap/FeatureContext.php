<?php

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\TableNode;
use TH\Docker\Links;

/**
 * Behat context class.
 */
class FeatureContext implements SnippetAcceptingContext
{
    private $env = [];
    private $links = null;

    /**
     * @BeforeFeature
     */
    private function resetEnv() {
        $this->env = [];
        $this->links = new Links([]);
    }

    /**
     * @Given there is the following environment variables
     */
    public function thereIsTheFollowingEnvironmentVariables(TableNode $env)
    {
        foreach ($env as $row) {
            $this->env[$row['name']] = $row['value'];
        }
    }

    /**
     * @When I parse the environment variables
     */
    public function iParseTheEnvironmentVariables()
    {
        $this->links = Links::buildFrom($this->env);
    }

    /**
     * @Then the link :link should have been found
     */
    public function theLinkShouldHaveBeenFound($link)
    {
        assert(isset($this->links[$link]));
    }

    /**
     * @Given the link :link name should be :name
     */
    public function theLinkNameShouldBe($link, $name)
    {
        assert($this->links[$link]->name() === $name);
    }

    /**
     * @Given the link :link main port number should be :portNumber
     */
    public function theLinkMainPortNumberShouldBe($link, $portNumber)
    {
        assert($this->links[$link]->mainPort()->number() === (int)$portNumber);
    }

    /**
     * @Given the link :link main address should be :address
     */
    public function theLinkMainAddressShouldBe($link, $address)
    {
        assert($this->links[$link]->mainPort()->address() === $address);
    }

    /**
     * @Given the link :link main protocol should be :protocol
     */
    public function theLinkMainProtocolShouldBe($link, $protocol)
    {
        assert($this->links[$link]->mainPort()->protocol() === strtoupper($protocol));
    }

    /**
     * @Given the link :link tcp port :port address should be :address
     */
    public function theLinkTcpPortAddressShouldBe($link, $port, $address)
    {
        assert($this->links[$link]->tcpPorts()[$port]->address() === $address);
    }

    /**
     * @Given the link :link tcp port :port url should be :url
     */
    public function theLinkTcpPortUrlShouldBe($link, $port, $url)
    {
        assert($this->links[$link]->tcpPorts()[$port]->url() === $url);
    }

    /**
     * @Then the link :link environment variable :name should be :value
     */
    public function theLinkEnvironmentVariableShouldBe($link, $name, $value)
    {
        assert($this->links[$link]->env()[$name] === $value);
    }
}
