<?php
namespace PHPCR\Test;

/**
 * Base class for the bootstrapping to load your phpcr implementation for the
 * test suite.
 *
 * See the README file Bootstrapping section for an introduction how this works
 */
abstract class AbstractLoader
{
    protected $factoryclass;
    protected $workspacename;

    /**
     * array with chapter names to skip all test cases in (without the numbers)
     */
    protected $unsupportedChapters = array();
    /**
     * array in the format Chapter\FeatureTest with all cases to skip
     */
    protected $unsupportedCases = array();
    /**
     * array in the format Chapter\FeatureTest::testName with all single tests to skip
     */
    protected $unsupportedTests = array();

    /**
     * Create the loader
     *
     * @param string $factoryclass the class name of your implementations
     *      RepositoryFactory. You can pass null but then you must overwrite
     *      the getRepository method.
     * @param string $workspacename the workspace to use for the tests, defaults to 'tests'
     */
    protected function __construct($factoryclass, $workspacename = 'tests')
    {
        $this->factoryclass = $factoryclass;
        $this->workspacename = $workspacename;
    }

    /**
     * The loader is a singleton.
     *
     * Implement this class to return an ImplementationLoader instance
     * configured to provide things from your implementation.
     *
     * @return AbstractLoader loader for your implementation
     */
    public abstract static function getInstance();

    /**
     * @return string classname of the repository factory
     */
    public function getRepositoryFactoryClass()
    {
        return $this->factoryclass;
    }

    /**
     * @return array hashmap with the parameters for the repository factory
     */
    public abstract function getRepositoryFactoryParameters();

    /**
     * You should overwrite this to instantiate the repository without the
     * factory.
     *
     * The default implementation uses the factory, but if the factory has an
     * error, you will get failing tests all over.
     *
     * @return PHPCR\RepositoryInstance the repository instance
     */
    public function getRepository()
    {
        $factoryclass = $this->getRepositoryFactoryClass();
        $factory = new $factoryclass;
        return $factory->getRepository($this->getRepositoryFactoryParameters());
    }

    /**
     * @return PHPCR\CredentialsInterface the login credentials that lead to successful login into the repository
     */
    public abstract function getCredentials();

    /**
     * @return PHPCR\CredentialsInterface the login credentials that lead to login failure
     */
    public abstract function getInvalidCredentials();

    /**
     * Used when impersonating another user in Reading\SessionReadMethodsTests::testImpersonate
     * And for Reading\SessionReadMethodsTest::testCheckPermissionAccessControlException
     *
     * The user may not have write access to /tests_general_base/numberPropertyNode/jcr:content/foo
     *
     * @return PHPCR\CredentialsInterface the login credentials with limited permissions for testing impersonate and access control
     */
    public abstract function getRestrictedCredentials();

    /**
     * @return string the user id that is used in the credentials
     */
    public abstract function getUserId();

    /**
     * @return string the workspace name used for the tests
     */
    public function getWorkspaceName()
    {
        return $this->workspacename;
    }

    /**
     * Get a session for this implementation.
     *
     * @param \PHPCR\CredentialsInterface $credentials The credentials to log into the repository. If omitted, self::getCredentials should be used
     * @return PHPCR\SessionInterface the session resulting from logging into the repository with the provided credentials
     */
    public function getSession($credentials = false)
    {
        $repository = $this->getRepository();
        if (false === $credentials) {
            $credentials = $this->getCredentials();
        }

        return $repository->login($credentials, $this->getWorkspaceName());
    }

    /**
     * Decide whether this test can be executed.
     *
     * The default implementation uses the unsupported... arrays to decide.
     * Overwrite if you need a different logic.
     *
     * @param string $chapter the chapter name (folder name)
     * @param string $class the test case name (filename without .php)
     * @param string $name the test name as returned by TestCase::getName()
     *
     * @return bool true if the implementation supports the features of this test
     */
    public function getTestSupported($testcase)
    {
        $fqn = get_class($testcase);
        list($phpcr, $tests, $chapter, $case) = explode('\\', $fqn);

        $case = "$chapter\\$case";
        $test = "$case::".$testcase->getName();

        return ! (   in_array($chapter, $this->unsupportedChapters)
                  || in_array($case, $this->unsupportedCases)
                  || in_array($test, $this->unsupportedTests)
                 );
    }

    /**
     * @return PHPCR\Test\FixtureLoaderInterface implementation that is used to load test fixtures
     */
    public abstract function getFixtureLoader();

}