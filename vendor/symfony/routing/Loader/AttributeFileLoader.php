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

use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\RouteCollection;

/**
 * AttributeFileLoader loads routing information from attributes set
 * on a PHP class and its methods.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 */
class AttributeFileLoader extends FileLoader
{
    protected AttributeClassLoader $loader;

    public function __construct(FileLocatorInterface $locator, AttributeClassLoader $loader)
    {
        if (! \function_exists('token_get_all')) {
            throw new \LogicException('The Tokenizer extension is required for the routing attribute loader.');
        }

        parent::__construct($locator);

        $this->loader = $loader;
    }

    /**
     * Loads from attributes from a file.
     *
     * @throws \InvalidArgumentException When the file does not exist or its routes cannot be parsed
     */
    public function load(mixed $file, ?string $type = null): ?RouteCollection
    {
        $path = $this->locator->locate($file);

        $collection = new RouteCollection();
        if ($class = $this->findClass($path)) {
            $refl = new \ReflectionClass($class);
            if ($refl->isAbstract()) {
                return null;
            }

            $collection->addResource(new FileResource($path));
            $collection->addCollection($this->loader->load($class, $type));
        }

        gc_mem_caches();

        return $collection;
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return \is_string($resource) && pathinfo($resource, \PATHINFO_EXTENSION) === 'php' && (! $type || $type === 'attribute');
    }

    /**
     * Returns the full class name for the first class in the file.
     */
    protected function findClass(string $file): string|false
    {
        $class = false;
        $namespace = false;
        $tokens = token_get_all(file_get_contents($file));

        if (\count($tokens) === 1 && $tokens[0][0] === \T_INLINE_HTML) {
            throw new \InvalidArgumentException(sprintf('The file "%s" does not contain PHP code. Did you forgot to add the "<?php" start tag at the beginning of the file?', $file));
        }

        $nsTokens = [\T_NS_SEPARATOR => true, \T_STRING => true];
        if (\defined('T_NAME_QUALIFIED')) {
            $nsTokens[\T_NAME_QUALIFIED] = true;
        }
        for ($i = 0; isset($tokens[$i]); $i++) {
            $token = $tokens[$i];
            if (! isset($token[1])) {
                continue;
            }

            if ($class === true && $token[0] === \T_STRING) {
                return $namespace.'\\'.$token[1];
            }

            if ($namespace === true && isset($nsTokens[$token[0]])) {
                $namespace = $token[1];
                while (isset($tokens[++$i][1], $nsTokens[$tokens[$i][0]])) {
                    $namespace .= $tokens[$i][1];
                }
                $token = $tokens[$i];
            }

            if ($token[0] === \T_CLASS) {
                // Skip usage of ::class constant and anonymous classes
                $skipClassToken = false;
                for ($j = $i - 1; $j > 0; $j--) {
                    if (! isset($tokens[$j][1])) {
                        if ($tokens[$j] === '(' || $tokens[$j] === ',') {
                            $skipClassToken = true;
                        }
                        break;
                    }

                    if ($tokens[$j][0] === \T_DOUBLE_COLON || $tokens[$j][0] === \T_NEW) {
                        $skipClassToken = true;
                        break;
                    } elseif (! \in_array($tokens[$j][0], [\T_WHITESPACE, \T_DOC_COMMENT, \T_COMMENT])) {
                        break;
                    }
                }

                if (! $skipClassToken) {
                    $class = true;
                }
            }

            if ($token[0] === \T_NAMESPACE) {
                $namespace = true;
            }
        }

        return false;
    }
}