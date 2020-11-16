<?php declare(strict_types=1);

namespace RichCongress\WebTestBundle\TestCase;

use RichCongress\TestFramework\TestConfiguration\Annotation\TestConfig;
use RichCongress\WebTestBundle\Exception\CsrfTokenManagerMissingException;
use RichCongress\WebTestBundle\TestCase\TestTrait\WebTestAssertionsTrait;
use RichCongress\WebTestBundle\WebTest\Client;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * Class ControllerTestCase
 *
 * @package   RichCongress\WebTestBundle\TestCase
 * @author    Nicolas Guilloux <nguilloux@richcongress.com>
 * @copyright 2014 - 2020 RichCongress (https://www.richcongress.com)
 * @TestConfig("kernel")
 */
abstract class ControllerTestCase extends TestCase
{
    use WebTestAssertionsTrait;

    /**
     * Get the CSRF token from the Type and the Client
     */
    protected function getCsrfToken(string $intention): string
    {
        try {
            /** @var CsrfTokenManagerInterface $csrfTokenManager */
            $csrfTokenManager = $this->getService(CsrfTokenManagerInterface::class);
        } catch (\Throwable $e) {
            throw new CsrfTokenManagerMissingException();
        }

        $class = null;

        if ($this->getContainer()->has($intention)) {
            $class = $this->getService($intention);
        } elseif (\is_subclass_of($intention, FormTypeInterface::class)) {
            $class = new $intention();
        }

        if ($class !== null) {
            $intention = $class->getBlockPrefix() ?? $intention;
        }

        return (string) $csrfTokenManager->getToken($intention);
    }

    /**
     * Transform an array of query parameters in a string for URL
     */
    protected static function parseQueryParams(array $queryParams): string
    {
        return '?' . \http_build_query($queryParams);
    }

    /**
     * @param KernelBrowser|Client $client
     * @return array<string|int, mixed>
     */
    protected static function getJsonContent($client, bool $assoc = true): array
    {
        $kernelBrowser = Client::extractBrowser($client);
        return \json_decode($kernelBrowser->getResponse()->getContent(), $assoc, 512, JSON_THROW_ON_ERROR);
    }
}
