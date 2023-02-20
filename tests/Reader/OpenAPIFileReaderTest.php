<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Reader;

use cebe\openapi\exceptions\UnresolvableReferenceException;
use cebe\openapi\spec\OpenApi;
use Membrane\OpenAPIRouter\Exception\CannotReadOpenAPI;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Exception\ParseException;
use TypeError;

#[CoversClass(OpenAPIFileReader::class)]
#[CoversClass(CannotReadOpenAPI::class)]
class OpenAPIFileReaderTest extends TestCase
{
    public const FIXTURES = __DIR__ . '/../fixtures/';

    public vfsStreamDirectory $vfsRoot;

    public function setUp(): void
    {
        $this->vfsRoot = vfsStream::setup();
    }

    #[Test]
    public function readerThrowsExceptionIfFileNotFound(): void
    {
        $filePath = $this->vfsRoot->url() . '/openapi.json';

        self::expectExceptionObject(CannotReadOpenAPI::fileNotFound($filePath));

        (new OpenAPIFileReader())->readFromAbsoluteFilePath($filePath);
    }

    #[Test]
    public function throwsExceptionForRelativeFilePaths(): void
    {
        self::expectExceptionObject(
            CannotReadOpenAPI::unresolvedReference('petstore.yaml', new UnresolvableReferenceException())
        );

        (new OpenAPIFileReader())->readFromAbsoluteFilePath('./tests/fixtures/docs/petstore.yaml');
    }

    #[Test]
    public function readerThrowsExceptionForUnsupportedFileTypes(): void
    {
        $structure = ['openapi.txt' => 'some text'];
        vfsStream::create($structure);
        $filePath = $this->vfsRoot->url() . '/openapi.txt';

        self::expectExceptionObject(CannotReadOpenAPI::fileTypeNotSupported(pathinfo($filePath, PATHINFO_EXTENSION)));

        (new OpenAPIFileReader())->readFromAbsoluteFilePath($filePath);
    }

    #[Test]
    public function readerThrowsExceptionIfYamlCannotBeParsedAsOpenAPI(): void
    {
        $structure = [
            'openapi.yaml' =>
                <<<YAML
                openapi: "
                YAML
        ];
        vfsStream::create($structure);
        $filePath = $this->vfsRoot->url() . '/openapi.yaml';

        self::expectExceptionObject(CannotReadOpenAPI::cannotParse('openapi.yaml', new ParseException('')));

        (new OpenAPIFileReader())->readFromAbsoluteFilePath($filePath);
    }

    #[Test]
    public function readerThrowsExceptionIfJsonCannotBeParsedAsOpenAPI(): void
    {
        $structure = [
            'openapi.json' =>
                <<<JSON
                {
                  "openapi": ",
                JSON
        ];
        vfsStream::create($structure);
        $filePath = $this->vfsRoot->url() . '/openapi.json';

        self::expectExceptionObject(CannotReadOpenAPI::cannotParse('openapi.json', new TypeError()));

        (new OpenAPIFileReader())->readFromAbsoluteFilePath($filePath);
    }

    #[Test]
    public function readerThrowsExceptionIfYamlContainsInvalidOpenAPI(): void
    {
        $structure = [
            'openapi.yaml' =>
                <<<YAML
                openapi: 3.0.0
                info:
                  title: "Test API"
                  version: "1.0.0"
                YAML
        ];
        vfsStream::create($structure);
        $filePath = $this->vfsRoot->url() . '/openapi.yaml';

        self::expectExceptionObject(CannotReadOpenAPI::invalidOpenAPI('openapi.yaml'));

        (new OpenAPIFileReader())->readFromAbsoluteFilePath($filePath);
    }

    #[Test]
    public function readerThrowsExceptionIfJsonContainsInvalidOpenAPI(): void
    {
        $structure = [
            'openapi.json' =>
                <<<JSON
                {
                  "openapi": "3.0.0",
                  "info": {
                    "title": "Test API",
                    "version": "1.0.0"
                  }
                }
                JSON
        ];
        vfsStream::create($structure);
        $filePath = $this->vfsRoot->url() . '/openapi.json';

        self::expectExceptionObject(CannotReadOpenAPI::invalidOpenAPI('openapi.json'));

        (new OpenAPIFileReader())->readFromAbsoluteFilePath($filePath);
    }


    #[Test]
    public function returnsOpenAPIObjectFromYamlWithValidOpenAPI(): void
    {
        $structure = [
            'openapi.yaml' =>
                <<<YAML
                openapi: 3.0.0
                info:
                  title: "Test API"
                  version: "1.0.0"
                paths:
                  /somepath:
                YAML
        ];
        vfsStream::create(structure: $structure);
        $filePath = $this->vfsRoot->url() . '/openapi.yaml';

        $actual = (new OpenAPIFileReader())->readFromAbsoluteFilePath($filePath);

        self::assertInstanceOf(OpenApi::class, $actual);
    }

    #[Test]
    public function returnsOpenAPIObjectFromJsonWithValidOpenAPI(): void
    {
        $structure = [
            'openapi.json' =>
                <<<JSON
                {
                  "openapi": "3.0.0",
                  "info": {
                    "title": "Test API",
                    "version": "1.0.0"
                  },
                  "paths": {
                  }
                }
                JSON
        ];
        vfsStream::create($structure);
        $filePath = $this->vfsRoot->url() . '/openapi.json';

        $actual = (new OpenAPIFileReader())->readFromAbsoluteFilePath($filePath);

        self::assertInstanceOf(OpenApi::class, $actual);
    }
}
