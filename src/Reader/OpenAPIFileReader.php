<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Reader;

use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\exceptions\UnresolvableReferenceException;
use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use Closure;
use Membrane\OpenAPIRouter\Exception\CannotReadOpenAPI;
use Symfony\Component\Yaml\Exception\ParseException;
use TypeError;

class OpenAPIFileReader
{
    /** @var Closure[] */
    private readonly array $supportedFileTypes;

    public function __construct()
    {
        $this->supportedFileTypes = [
            'json' => fn($p) => Reader::readFromJsonFile(fileName: $p, resolveReferences: false),
            'yaml' => fn($p) => Reader::readFromYamlFile(fileName: $p, resolveReferences: false),
            'yml' => fn($p) => Reader::readFromYamlFile(fileName: $p, resolveReferences: false),
        ];
    }

    public function readFromAbsoluteFilePath(string $absoluteFilePath): OpenApi
    {
        file_exists($absoluteFilePath) ?: throw CannotReadOpenAPI::fileNotFound($absoluteFilePath);

        $fileType = strtolower(pathinfo($absoluteFilePath, PATHINFO_EXTENSION));

        $readFrom = $this->supportedFileTypes[$fileType] ?? throw CannotReadOpenAPI::fileTypeNotSupported($fileType);

        try {
            $openAPI = $readFrom($absoluteFilePath);
        } catch (TypeError | TypeErrorException | ParseException $e) {
            throw CannotReadOpenAPI::cannotParse(pathinfo($absoluteFilePath, PATHINFO_BASENAME), $e);
        } catch (UnresolvableReferenceException $e) {
            throw CannotReadOpenAPI::unresolvedReference(pathinfo($absoluteFilePath, PATHINFO_BASENAME), $e);
        }

        $openAPI->validate() ?: throw CannotReadOpenAPI::invalidOpenAPI(pathinfo($absoluteFilePath, PATHINFO_BASENAME));

        return $openAPI;
    }
}
