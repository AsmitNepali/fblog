<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Routing\Loader;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Config\Util\XmlUtils;
use Symfony\Component\Routing\Loader\Configurator\Traits\HostTrait;
use Symfony\Component\Routing\Loader\Configurator\Traits\LocalizedRouteTrait;
use Symfony\Component\Routing\Loader\Configurator\Traits\PrefixTrait;
use Symfony\Component\Routing\RouteCollection;

/**
 * XmlFileLoader loads XML routing files.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Tobias Schultze <http://tobion.de>
 */
class XmlFileLoader extends FileLoader
{
    use HostTrait;
    use LocalizedRouteTrait;
    use PrefixTrait;

    public const NAMESPACE_URI = 'http://symfony.com/schema/routing';

    public const SCHEME_PATH = '/schema/routing/routing-1.0.xsd';

    /**
     * @throws \InvalidArgumentException when the file cannot be loaded or when the XML cannot be
     *                                   parsed because it does not validate against the scheme
     */
    public function load(mixed $file, ?string $type = null): RouteCollection
    {
        $path = $this->locator->locate($file);

        $xml = $this->loadFile($path);

        $collection = new RouteCollection();
        $collection->addResource(new FileResource($path));

        // process routes and imports
        foreach ($xml->documentElement->childNodes as $node) {
            if (! $node instanceof \DOMElement) {
                continue;
            }

            $this->parseNode($collection, $node, $path, $file);
        }

        return $collection;
    }

    /**
     * Parses a node from a loaded XML file.
     *
     * @throws \InvalidArgumentException When the XML is invalid
     */
    protected function parseNode(RouteCollection $collection, \DOMElement $node, string $path, string $file): void
    {
        if ($node->namespaceURI !== self::NAMESPACE_URI) {
            return;
        }

        switch ($node->localName) {
            case 'route':
                $this->parseRoute($collection, $node, $path);
                break;
            case 'import':
                $this->parseImport($collection, $node, $path, $file);
                break;
            case 'when':
                if (! $this->env || $node->getAttribute('env') !== $this->env) {
                    break;
                }
                foreach ($node->childNodes as $node) {
                    if ($node instanceof \DOMElement) {
                        $this->parseNode($collection, $node, $path, $file);
                    }
                }
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unknown tag "%s" used in file "%s". Expected "route" or "import".', $node->localName, $path));
        }
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return \is_string($resource) && pathinfo($resource, \PATHINFO_EXTENSION) === 'xml' && (! $type || $type === 'xml');
    }

    /**
     * Parses a route and adds it to the RouteCollection.
     *
     * @throws \InvalidArgumentException When the XML is invalid
     */
    protected function parseRoute(RouteCollection $collection, \DOMElement $node, string $path): void
    {
        if ('' === $id = $node->getAttribute('id')) {
            throw new \InvalidArgumentException(sprintf('The <route> element in file "%s" must have an "id" attribute.', $path));
        }

        if ('' !== $alias = $node->getAttribute('alias')) {
            $alias = $collection->addAlias($id, $alias);

            if ($deprecationInfo = $this->parseDeprecation($node, $path)) {
                $alias->setDeprecated($deprecationInfo['package'], $deprecationInfo['version'], $deprecationInfo['message']);
            }

            return;
        }

        $schemes = preg_split('/[\s,\|]++/', $node->getAttribute('schemes'), -1, \PREG_SPLIT_NO_EMPTY);
        $methods = preg_split('/[\s,\|]++/', $node->getAttribute('methods'), -1, \PREG_SPLIT_NO_EMPTY);

        [$defaults, $requirements, $options, $condition, $paths, /* $prefixes */, $hosts] = $this->parseConfigs($node, $path);

        if (! $paths && $node->getAttribute('path') === '') {
            throw new \InvalidArgumentException(sprintf('The <route> element in file "%s" must have a "path" attribute or <path> child nodes.', $path));
        }

        if ($paths && $node->getAttribute('path') !== '') {
            throw new \InvalidArgumentException(sprintf('The <route> element in file "%s" must not have both a "path" attribute and <path> child nodes.', $path));
        }

        $routes = $this->createLocalizedRoute($collection, $id, $paths ?: $node->getAttribute('path'));
        $routes->addDefaults($defaults);
        $routes->addRequirements($requirements);
        $routes->addOptions($options);
        $routes->setSchemes($schemes);
        $routes->setMethods($methods);
        $routes->setCondition($condition);

        if ($hosts !== null) {
            $this->addHost($routes, $hosts);
        }
    }

    /**
     * Parses an import and adds the routes in the resource to the RouteCollection.
     *
     * @throws \InvalidArgumentException When the XML is invalid
     */
    protected function parseImport(RouteCollection $collection, \DOMElement $node, string $path, string $file): void
    {
        /** @var \DOMElement $resourceElement */
        if (! ($resource = $node->getAttribute('resource') ?: null) && $resourceElement = $node->getElementsByTagName('resource')[0] ?? null) {
            $resource = [];
            /** @var \DOMAttr $attribute */
            foreach ($resourceElement->attributes as $attribute) {
                $resource[$attribute->name] = $attribute->value;
            }
        }

        if (! $resource) {
            throw new \InvalidArgumentException(sprintf('The <import> element in file "%s" must have a "resource" attribute or element.', $path));
        }

        $type = $node->getAttribute('type');
        $prefix = $node->getAttribute('prefix');
        $schemes = $node->hasAttribute('schemes') ? preg_split('/[\s,\|]++/', $node->getAttribute('schemes'), -1, \PREG_SPLIT_NO_EMPTY) : null;
        $methods = $node->hasAttribute('methods') ? preg_split('/[\s,\|]++/', $node->getAttribute('methods'), -1, \PREG_SPLIT_NO_EMPTY) : null;
        $trailingSlashOnRoot = $node->hasAttribute('trailing-slash-on-root') ? XmlUtils::phpize($node->getAttribute('trailing-slash-on-root')) : true;
        $namePrefix = $node->getAttribute('name-prefix') ?: null;

        [$defaults, $requirements, $options, $condition, /* $paths */, $prefixes, $hosts] = $this->parseConfigs($node, $path);

        if ($prefix !== '' && $prefixes) {
            throw new \InvalidArgumentException(sprintf('The <route> element in file "%s" must not have both a "prefix" attribute and <prefix> child nodes.', $path));
        }

        $exclude = [];
        foreach ($node->childNodes as $child) {
            if ($child instanceof \DOMElement && $child->localName === $exclude && $child->namespaceURI === self::NAMESPACE_URI) {
                $exclude[] = $child->nodeValue;
            }
        }

        if ($node->hasAttribute('exclude')) {
            if ($exclude) {
                throw new \InvalidArgumentException('You cannot use both the attribute "exclude" and <exclude> tags at the same time.');
            }
            $exclude = [$node->getAttribute('exclude')];
        }

        $this->setCurrentDir(\dirname($path));

        /** @var RouteCollection[] $imported */
        $imported = $this->import($resource, $type !== '' ? $type : null, false, $file, $exclude) ?: [];

        if (! \is_array($imported)) {
            $imported = [$imported];
        }

        foreach ($imported as $subCollection) {
            $this->addPrefix($subCollection, $prefixes ?: $prefix, $trailingSlashOnRoot);

            if ($hosts !== null) {
                $this->addHost($subCollection, $hosts);
            }

            if ($condition !== null) {
                $subCollection->setCondition($condition);
            }
            if ($schemes !== null) {
                $subCollection->setSchemes($schemes);
            }
            if ($methods !== null) {
                $subCollection->setMethods($methods);
            }
            if ($namePrefix !== null) {
                $subCollection->addNamePrefix($namePrefix);
            }
            $subCollection->addDefaults($defaults);
            $subCollection->addRequirements($requirements);
            $subCollection->addOptions($options);

            $collection->addCollection($subCollection);
        }
    }

    /**
     * @throws \InvalidArgumentException When loading of XML file fails because of syntax errors
     *                                   or when the XML structure is not as expected by the scheme -
     *                                   see validate()
     */
    protected function loadFile(string $file): \DOMDocument
    {
        return XmlUtils::loadFile($file, __DIR__.static::SCHEME_PATH);
    }

    /**
     * Parses the config elements (default, requirement, option).
     *
     * @throws \InvalidArgumentException When the XML is invalid
     */
    private function parseConfigs(\DOMElement $node, string $path): array
    {
        $defaults = [];
        $requirements = [];
        $options = [];
        $condition = null;
        $prefixes = [];
        $paths = [];
        $hosts = [];

        /** @var \DOMElement $n */
        foreach ($node->getElementsByTagNameNS(self::NAMESPACE_URI, '*') as $n) {
            if ($node !== $n->parentNode) {
                continue;
            }

            switch ($n->localName) {
                case 'path':
                    $paths[$n->getAttribute('locale')] = trim($n->textContent);
                    break;
                case 'host':
                    $hosts[$n->getAttribute('locale')] = trim($n->textContent);
                    break;
                case 'prefix':
                    $prefixes[$n->getAttribute('locale')] = trim($n->textContent);
                    break;
                case 'default':
                    if ($this->isElementValueNull($n)) {
                        $defaults[$n->getAttribute('key')] = null;
                    } else {
                        $defaults[$n->getAttribute('key')] = $this->parseDefaultsConfig($n, $path);
                    }

                    break;
                case 'requirement':
                    $requirements[$n->getAttribute('key')] = trim($n->textContent);
                    break;
                case 'option':
                    $options[$n->getAttribute('key')] = XmlUtils::phpize(trim($n->textContent));
                    break;
                case 'condition':
                    $condition = trim($n->textContent);
                    break;
                case 'resource':
                    break;
                default:
                    throw new \InvalidArgumentException(sprintf('Unknown tag "%s" used in file "%s". Expected "default", "requirement", "option" or "condition".', $n->localName, $path));
            }
        }

        if ($controller = $node->getAttribute('controller')) {
            if (isset($defaults['_controller'])) {
                $name = $node->hasAttribute('id') ? sprintf('"%s".', $node->getAttribute('id')) : sprintf('the "%s" tag.', $node->tagName);

                throw new \InvalidArgumentException(sprintf('The routing file "%s" must not specify both the "controller" attribute and the defaults key "_controller" for ', $path).$name);
            }

            $defaults['_controller'] = $controller;
        }
        if ($node->hasAttribute('locale')) {
            $defaults['_locale'] = $node->getAttribute('locale');
        }
        if ($node->hasAttribute('format')) {
            $defaults['_format'] = $node->getAttribute('format');
        }
        if ($node->hasAttribute('utf8')) {
            $options['utf8'] = XmlUtils::phpize($node->getAttribute('utf8'));
        }
        if ($stateless = $node->getAttribute('stateless')) {
            if (isset($defaults['_stateless'])) {
                $name = $node->hasAttribute('id') ? sprintf('"%s".', $node->getAttribute('id')) : sprintf('the "%s" tag.', $node->tagName);

                throw new \InvalidArgumentException(sprintf('The routing file "%s" must not specify both the "stateless" attribute and the defaults key "_stateless" for ', $path).$name);
            }

            $defaults['_stateless'] = XmlUtils::phpize($stateless);
        }

        if (! $hosts) {
            $hosts = $node->hasAttribute('host') ? $node->getAttribute('host') : null;
        }

        return [$defaults, $requirements, $options, $condition, $paths, $prefixes, $hosts];
    }

    /**
     * Parses the "default" elements.
     */
    private function parseDefaultsConfig(\DOMElement $element, string $path): array|bool|float|int|string|null
    {
        if ($this->isElementValueNull($element)) {
            return null;
        }

        // Check for existing element nodes in the default element. There can
        // only be a single element inside a default element. So this element
        // (if one was found) can safely be returned.
        foreach ($element->childNodes as $child) {
            if (! $child instanceof \DOMElement) {
                continue;
            }

            if ($child->namespaceURI !== self::NAMESPACE_URI) {
                continue;
            }

            return $this->parseDefaultNode($child, $path);
        }

        // If the default element doesn't contain a nested "bool", "int", "float",
        // "string", "list", or "map" element, the element contents will be treated
        // as the string value of the associated default option.
        return trim($element->textContent);
    }

    /**
     * Recursively parses the value of a "default" element.
     *
     * @throws \InvalidArgumentException when the XML is invalid
     */
    private function parseDefaultNode(\DOMElement $node, string $path): array|bool|float|int|string|null
    {
        if ($this->isElementValueNull($node)) {
            return null;
        }

        switch ($node->localName) {
            case 'bool':
                return trim($node->nodeValue) === 'true' || trim($node->nodeValue) === '1';
            case 'int':
                return (int) trim($node->nodeValue);
            case 'float':
                return (float) trim($node->nodeValue);
            case 'string':
                return trim($node->nodeValue);
            case 'list':
                $list = [];

                foreach ($node->childNodes as $element) {
                    if (! $element instanceof \DOMElement) {
                        continue;
                    }

                    if ($element->namespaceURI !== self::NAMESPACE_URI) {
                        continue;
                    }

                    $list[] = $this->parseDefaultNode($element, $path);
                }

                return $list;
            case 'map':
                $map = [];

                foreach ($node->childNodes as $element) {
                    if (! $element instanceof \DOMElement) {
                        continue;
                    }

                    if ($element->namespaceURI !== self::NAMESPACE_URI) {
                        continue;
                    }

                    $map[$element->getAttribute('key')] = $this->parseDefaultNode($element, $path);
                }

                return $map;
            default:
                throw new \InvalidArgumentException(sprintf('Unknown tag "%s" used in file "%s". Expected "bool", "int", "float", "string", "list", or "map".', $node->localName, $path));
        }
    }

    private function isElementValueNull(\DOMElement $element): bool
    {
        $namespaceUri = 'http://www.w3.org/2001/XMLSchema-instance';

        if (! $element->hasAttributeNS($namespaceUri, 'nil')) {
            return false;
        }

        return $element->getAttributeNS($namespaceUri, 'nil') === 'true' || $element->getAttributeNS($namespaceUri, 'nil') === '1';
    }

    /**
     * Parses the deprecation elements.
     *
     * @throws \InvalidArgumentException When the XML is invalid
     */
    private function parseDeprecation(\DOMElement $node, string $path): array
    {
        $deprecatedNode = null;
        foreach ($node->childNodes as $child) {
            if (! $child instanceof \DOMElement || $child->namespaceURI !== self::NAMESPACE_URI) {
                continue;
            }
            if ($child->localName !== 'deprecated') {
                throw new \InvalidArgumentException(sprintf('Invalid child element "%s" defined for alias "%s" in "%s".', $child->localName, $node->getAttribute('id'), $path));
            }

            $deprecatedNode = $child;
        }

        if ($deprecatedNode === null) {
            return [];
        }

        if (! $deprecatedNode->hasAttribute('package')) {
            throw new \InvalidArgumentException(sprintf('The <deprecated> element in file "%s" must have a "package" attribute.', $path));
        }
        if (! $deprecatedNode->hasAttribute('version')) {
            throw new \InvalidArgumentException(sprintf('The <deprecated> element in file "%s" must have a "version" attribute.', $path));
        }

        return [
            'package' => $deprecatedNode->getAttribute('package'),
            'version' => $deprecatedNode->getAttribute('version'),
            'message' => trim($deprecatedNode->nodeValue),
        ];
    }
}
