<?php

declare(strict_types=1);

namespace Deyvo\Core\Dashboard;

use InvalidArgumentException;
use JsonException;

final class DashboardSchema
{
    private const FieldTypes = [
        'text',
        'textarea',
        'email',
        'url',
        'select',
        'boolean',
    ];

    private function __construct(
        private array $pages,
    ) {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public static function fromJson(string $json): self
    {
        try {
            $definition = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException('Deyvo dashboard schema must contain valid JSON.', previous: $exception);
        }

        if (! is_array($definition)) {
            throw new InvalidArgumentException('Deyvo dashboard schema must be a JSON object.');
        }

        return self::fromArray($definition);
    }

    public function pages(): array
    {
        return $this->pages;
    }

    public function page(string $key): ?array
    {
        foreach ($this->pages as $page) {
            if ($page['key'] === $key) {
                return $page;
            }
        }

        return null;
    }

    private static function fromArray(array $definition): self
    {
        if (array_is_list($definition)) {
            throw new InvalidArgumentException('Deyvo dashboard schema must be a JSON object.');
        }

        $definitions = $definition['pages'] ?? [];

        if (! is_array($definitions) || ! array_is_list($definitions)) {
            throw new InvalidArgumentException('Deyvo dashboard schema pages must be a JSON array.');
        }

        $pages = [];
        $keys = [];

        foreach ($definitions as $definition) {
            if (! is_array($definition) || array_is_list($definition)) {
                throw new InvalidArgumentException('Every Deyvo dashboard page must be a JSON object.');
            }

            $page = self::pageDefinition($definition);

            if (in_array($page['key'], $keys, true)) {
                throw new InvalidArgumentException("Deyvo dashboard page key [{$page['key']}] is duplicated.");
            }

            $keys[] = $page['key'];
            $pages[] = $page;
        }

        usort($pages, static fn (array $left, array $right): int => $left['sort'] <=> $right['sort']);

        return new self($pages);
    }

    private static function pageDefinition(array $definition): array
    {
        $key = self::requiredString($definition['key'] ?? null, 'page key', 80);

        if (preg_match('/^[a-z0-9][a-z0-9-]*$/', $key) !== 1) {
            throw new InvalidArgumentException("Deyvo dashboard page key [{$key}] is invalid.");
        }

        $fields = $definition['fields'] ?? null;

        if (! is_array($fields) || ! array_is_list($fields) || $fields === []) {
            throw new InvalidArgumentException("Deyvo dashboard page [{$key}] must define fields.");
        }

        $parsedFields = [];
        $fieldKeys = [];

        foreach ($fields as $field) {
            if (! is_array($field) || array_is_list($field)) {
                throw new InvalidArgumentException("Deyvo dashboard page [{$key}] contains an invalid field.");
            }

            $parsedField = self::fieldDefinition($field);

            if (in_array($parsedField['key'], $fieldKeys, true)) {
                throw new InvalidArgumentException("Deyvo dashboard field key [{$parsedField['key']}] is duplicated.");
            }

            $fieldKeys[] = $parsedField['key'];
            $parsedFields[] = $parsedField;
        }

        return [
            'key' => $key,
            'label' => self::requiredString($definition['label'] ?? null, 'page label', 120),
            'description' => self::optionalString($definition['description'] ?? null, 'page description', 500),
            'sort' => self::sort($definition['sort'] ?? 100, 'page sort'),
            'fields' => $parsedFields,
        ];
    }

    private static function fieldDefinition(array $definition): array
    {
        $key = self::requiredString($definition['key'] ?? null, 'field key', 120);

        if (preg_match('/^[a-z0-9][a-z0-9._-]*$/', $key) !== 1) {
            throw new InvalidArgumentException("Deyvo dashboard field key [{$key}] is invalid.");
        }

        $type = $definition['type'] ?? 'text';

        if (! is_string($type) || ! in_array($type, self::FieldTypes, true)) {
            throw new InvalidArgumentException("Deyvo dashboard field [{$key}] has an invalid type.");
        }

        $storage = $definition['storage'] ?? 'setting';

        if (! is_string($storage) || ! in_array($storage, ['setting', 'content'], true)) {
            throw new InvalidArgumentException("Deyvo dashboard field [{$key}] has an invalid storage type.");
        }

        $required = $definition['required'] ?? false;

        if (! is_bool($required)) {
            throw new InvalidArgumentException("Deyvo dashboard field [{$key}] required must be a boolean.");
        }

        $published = $definition['published'] ?? true;

        if (! is_bool($published)) {
            throw new InvalidArgumentException("Deyvo dashboard field [{$key}] published must be a boolean.");
        }

        return [
            'key' => $key,
            'label' => self::requiredString($definition['label'] ?? null, 'field label', 160),
            'type' => $type,
            'storage' => $storage,
            'required' => $required,
            'help' => self::optionalString($definition['help'] ?? null, 'field help', 500),
            'placeholder' => self::optionalString($definition['placeholder'] ?? null, 'field placeholder', 200),
            'options' => self::options($definition['options'] ?? null, $type, $key),
            'content_title' => self::optionalString($definition['content_title'] ?? null, 'content title', 160),
            'published' => $published,
        ];
    }

    private static function options(mixed $options, string $type, string $key): array
    {
        if ($type !== 'select') {
            if ($options !== null) {
                throw new InvalidArgumentException("Deyvo dashboard field [{$key}] options are only available for select fields.");
            }

            return [];
        }

        if (! is_array($options) || ! array_is_list($options) || $options === []) {
            throw new InvalidArgumentException("Deyvo dashboard select field [{$key}] must define options.");
        }

        $parsed = [];
        $values = [];

        foreach ($options as $option) {
            if (! is_array($option) || array_is_list($option)) {
                throw new InvalidArgumentException("Deyvo dashboard select field [{$key}] contains an invalid option.");
            }

            $value = self::requiredString($option['value'] ?? null, 'option value', 120);

            if (in_array($value, $values, true)) {
                throw new InvalidArgumentException("Deyvo dashboard select field [{$key}] contains a duplicate option value.");
            }

            $values[] = $value;
            $parsed[] = [
                'value' => $value,
                'label' => self::requiredString($option['label'] ?? null, 'option label', 160),
            ];
        }

        return $parsed;
    }

    private static function requiredString(mixed $value, string $name, int $maximumLength): string
    {
        if (! is_string($value) || trim($value) === '') {
            throw new InvalidArgumentException("Deyvo dashboard {$name} must be a non-empty string.");
        }

        if (strlen($value) > $maximumLength) {
            throw new InvalidArgumentException("Deyvo dashboard {$name} may not exceed {$maximumLength} characters.");
        }

        return $value;
    }

    private static function optionalString(mixed $value, string $name, int $maximumLength): ?string
    {
        if ($value === null) {
            return null;
        }

        return self::requiredString($value, $name, $maximumLength);
    }

    private static function sort(mixed $value, string $name): int
    {
        if (! is_int($value)) {
            throw new InvalidArgumentException("Deyvo dashboard {$name} must be an integer.");
        }

        return $value;
    }
}
