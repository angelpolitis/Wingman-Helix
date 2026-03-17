<?php
    /**
     * Project Name:    Wingman Helix - Proxy Tests
     * Created by:      Angel Politis
     * Creation Date:   Mar 16 2026
     * Last Modified:   Mar 17 2026
     *
     * Copyright (c) 2026-2026 Angel Politis <info@angelpolitis.com>
     * This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0.
     * If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
     */

    namespace Wingman\Helix\Tests;

    use RuntimeException;
    use Throwable;
    use Wingman\Argus\Attributes\Define;
    use Wingman\Argus\Attributes\Group;
    use Wingman\Argus\Test;
    use Wingman\Helix\Inspector;
    use Wingman\Helix\Proxy;
    use Wingman\Helix\Tests\Fixtures\SampleClass;

    require_once __DIR__ . "/Fixtures.php";

    /**
     * Tests for the Proxy class: method forwarding, translation maps, cache
     * behaviour, visibility bypass, and factory / accessor methods.
     * @package Wingman\Helix\Tests
     * @author Angel Politis <info@angelpolitis.com>
     * @since 1.0
     */
    class ProxyTest extends Test {

        /**
         * Resets the Inspector singleton after each test.
         */
        public function tearDown () : void {
            Inspector::setInstance(new Inspector());
        }

        // ─── __call() ─────────────────────────────────────────────────────────────

        #[Group("Proxy")]
        #[Define(
            name: "__call() — Forwards To Target Method",
            description: "__call() delegates to the target's public method and returns the correct result."
        )]
        public function testCallForwardsToTargetMethod () : void {
            $target = new SampleClass("Alice");
            $proxy = new Proxy($target);

            $this->assertEquals("Alice", $proxy->getName());
        }

        #[Group("Proxy")]
        #[Define(
            name: "__call() — Throws RuntimeException For Unknown Method",
            description: "__call() throws a RuntimeException when neither the map nor the target has the requested method."
        )]
        public function testCallThrowsForUnknownMethod () : void {
            $proxy = new Proxy(new SampleClass("Alice"));

            $this->assertThrows(
                RuntimeException::class,
                fn () => $proxy->noSuchMethod()
            );
        }

        #[Group("Proxy")]
        #[Define(
            name: "__call() — Uses Map To Translate Method Name",
            description: "When a map entry exists, __call() invokes the mapped target method instead of the literal one."
        )]
        public function testCallUsesMapToTranslateMethodName () : void {
            $target = new SampleClass("Alice");
            $proxy = new Proxy($target, ["fetchName" => "getName"]);

            $this->assertEquals("Alice", $proxy->fetchName());
        }

        #[Group("Proxy")]
        #[Define(
            name: "__call() — Caches Closure After First Call",
            description: "Calling the same method twice reuses the cached closure; the second call still returns a correct result."
        )]
        public function testCallCachesClosureAfterFirstCall () : void {
            $target = new SampleClass("Bob");
            $proxy = new Proxy($target);

            $first = $proxy->getName();
            $second = $proxy->getName();

            $this->assertEquals($first, $second);
        }

        // ─── clearCache() ─────────────────────────────────────────────────────────

        #[Group("Proxy")]
        #[Define(
            name: "clearCache() — Removes All Cached Closures",
            description: "After clearCache(), subsequent calls still work — they just re-bind the closures."
        )]
        public function testClearCacheRemovesCachedClosures () : void {
            $target = new SampleClass("Charlie");
            $proxy = new Proxy($target);

            $proxy->getName();
            $proxy->clearCache();

            # Still works after clearing — just re-binds.
            $this->assertEquals("Charlie", $proxy->getName());
        }

        #[Group("Proxy")]
        #[Define(
            name: "clearCache() — Returns Self For Chaining",
            description: "clearCache() returns the same proxy instance."
        )]
        public function testClearCacheReturnsSelf () : void {
            $proxy = new Proxy(new SampleClass("x"));

            $this->assertTrue($proxy === $proxy->clearCache());
        }

        // ─── from() ───────────────────────────────────────────────────────────────

        #[Group("Proxy")]
        #[Define(
            name: "from() — Creates Proxy With Target",
            description: "The static factory wraps the given target and returns a Proxy instance."
        )]
        public function testFromCreatesProxyWithTarget () : void {
            $target = new SampleClass("Dave");
            $proxy = Proxy::from($target);

            $this->assertInstanceOf(Proxy::class, $proxy);
            $this->assertTrue($proxy->getTarget() === $target);
        }

        // ─── map() ────────────────────────────────────────────────────────────────

        #[Group("Proxy")]
        #[Define(
            name: "map() — Adds Mapping And Clears Cache Entry",
            description: "map() registers a new translation and removes the old cached closure so the next call re-binds."
        )]
        public function testMapAddsMappingAndClearsCache () : void {
            $target = new SampleClass("Eve");
            $proxy = new Proxy($target);

            # Warm the cache.
            $proxy->setName("Eve");
            # Re-map to a different target method.
            $proxy->map("rename", "setName");

            $proxy->rename("Eve2");

            $this->assertEquals("Eve2", $proxy->getName());
        }

        // ─── hasMethod() ──────────────────────────────────────────────────────────

        #[Group("Proxy")]
        #[Define(
            name: "hasMethod() — Returns True For Existing Method",
            description: "hasMethod() returns true when the target has the method (considering the map)."
        )]
        public function testHasMethodReturnsTrueForExistingMethod () : void {
            $proxy = new Proxy(new SampleClass("x"));

            $this->assertTrue($proxy->hasMethod("getName"));
        }

        #[Group("Proxy")]
        #[Define(
            name: "hasMethod() — Returns False For Missing Method",
            description: "hasMethod() returns false when neither the map nor the target has the method."
        )]
        public function testHasMethodReturnsFalseForMissingMethod () : void {
            $proxy = new Proxy(new SampleClass("x"));

            $this->assertFalse($proxy->hasMethod("noSuchMethod"));
        }

        #[Group("Proxy")]
        #[Define(
            name: "hasMethod() — Respects Map Entries",
            description: "hasMethod() returns true for a proxy-side name that is mapped to a real target method."
        )]
        public function testHasMethodRespectsMapEntries () : void {
            $proxy = new Proxy(new SampleClass("x"), ["fetchName" => "getName"]);

            $this->assertTrue($proxy->hasMethod("fetchName"));
        }

        // ─── getTarget() ──────────────────────────────────────────────────────────

        #[Group("Proxy")]
        #[Define(
            name: "getTarget() — Returns The Wrapped Object",
            description: "getTarget() returns exactly the object that was passed to the constructor."
        )]
        public function testGetTargetReturnsWrappedObject () : void {
            $target = new SampleClass("x");
            $proxy = new Proxy($target);

            $this->assertTrue($proxy->getTarget() === $target);
        }

        // ─── getMethodSignature() ─────────────────────────────────────────────────

        #[Group("Proxy")]
        #[Define(
            name: "getMethodSignature() — Returns Signature String For Known Method",
            description: "getMethodSignature() returns a non-empty string describing the target method's signature."
        )]
        public function testGetMethodSignatureReturnsNonEmptyString () : void {
            $proxy = new Proxy(new SampleClass("x"));
            $sig = $proxy->getMethodSignature("getName");

            $this->assertTrue(strlen($sig) > 0);
        }

        #[Group("Proxy")]
        #[Define(
            name: "getMethodSignature() — Throws RuntimeException For Missing Method",
            description: "getMethodSignature() throws a RuntimeException when the method does not exist."
        )]
        public function testGetMethodSignatureThrowsForMissingMethod () : void {
            $proxy = new Proxy(new SampleClass("x"));

            $this->assertThrows(
                RuntimeException::class,
                fn () => $proxy->getMethodSignature("noSuchMethod")
            );
        }

        // ─── Visibility Bypass ────────────────────────────────────────────────────

        #[Group("Proxy")]
        #[Define(
            name: "Visibility Bypass — Accesses Private Method",
            description: "Closure::bind() allows the proxy to invoke a private method on the target without directly calling it."
        )]
        public function testVisibilityBypassAllowsPrivateMethodAccess () : void {
            $target = new SampleClass("Alice");
            $proxy = new Proxy($target);

            # 'internalReset' is a private method on SampleClass; the proxy
            # should invoke it without a visibility error.
            $this->assertNotThrows(Throwable::class, fn () => $proxy->internalReset());
        }

        #[Group("Proxy")]
        #[Define(
            name: "Visibility Bypass — Accesses Protected Method",
            description: "The proxy can invoke a protected method on the target through its closed-over binding."
        )]
        public function testVisibilityBypassAllowsProtectedMethodAccess () : void {
            $target = new SampleClass("Alice");
            $proxy = new Proxy($target);

            $result = $proxy->getCount();

            $this->assertEquals(0, $result);
        }
    }
?>
