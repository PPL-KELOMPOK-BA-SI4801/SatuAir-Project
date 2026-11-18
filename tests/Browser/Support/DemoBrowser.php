<?php

namespace Tests\Browser\Support;

use Laravel\Dusk\Browser;

class DemoBrowser extends Browser
{
    public function deleteAllCookies(): static
    {
        $this->driver->manage()->deleteAllCookies();
        return $this;
    }

    public function assertElementsCount(string $selector, int $count): static
    {
        \PHPUnit\Framework\Assert::assertCount($count, $this->resolver->all($selector));
        return $this;
    }

    private function step(): static
    {
        $delay = getenv('DUSK_STEP_DELAY');
        return $this->pause($delay === false ? 650 : max(0, (int) $delay));
    }

    public function visit($url) { parent::visit($url); return $this->step(); }
    public function refresh() { parent::refresh(); return $this->step(); }
    public function type($field, $value)
    {
        $element = $this->resolver->resolveForTyping($field);
        if (! $element->isDisplayed() || ! $element->isEnabled() || $element->getAttribute('readonly')) {
            throw new \RuntimeException('Input tidak dapat diisi: '.$field);
        }
        $typeDelay = getenv('DUSK_TYPE_DELAY');
        $typeDelay = $typeDelay === false ? 0 : max(0, (int) $typeDelay);
        // Browser-side fill, equivalent to editing the actual control: dispatch
        // input/change so Alpine and meter.js run. Never assign calculated totals.
        // Avoid OS keyboard focus/modifier interference during a visible demo.
        $this->driver->executeAsyncScript(<<<'JS'
            const el = arguments[0], text = arguments[1], delay = arguments[2], done = arguments[3];
            if (el.maxLength >= 0 && text.length > el.maxLength) { done('Input melewati maxlength browser'); return; }
            el.scrollIntoView({block:'center'}); el.focus();
            const proto = el.tagName === 'TEXTAREA' ? HTMLTextAreaElement.prototype : HTMLInputElement.prototype;
            const setter = Object.getOwnPropertyDescriptor(proto, 'value').set;
            const assign = value => { setter.call(el, value); el.dispatchEvent(new Event('input', {bubbles:true})); };
            (async () => {
                if (delay > 0) {
                    assign(''); let current = '';
                    for (const ch of text) { current += ch; assign(current); await new Promise(r => setTimeout(r, delay)); }
                } else assign(text);
                el.dispatchEvent(new Event('change', {bubbles:true})); done(null);
            })().catch(e => done(e.message));
            JS, [$element, (string) $value, $typeDelay]);
        \PHPUnit\Framework\Assert::assertSame((string) $value, $this->resolver->resolveForTyping($field)->getAttribute('value'), 'Input browser tidak sesuai teks uji: '.$field);
        return $this->step();
    }
    public function press($button)
    {
        $element = $this->resolver->resolveForButtonPress($button);
        $this->driver->executeScript('arguments[0].scrollIntoView({block:"center"})', [$element]);
        if (! $element->isDisplayed() || ! $element->isEnabled()) {
            throw new \RuntimeException('Tombol tidak dapat ditekan: '.$button);
        }
        // Like Dusk's clickLink: dispatch a DOM click in the real browser. This
        // avoids lost native clicks when Windows changes foreground windows.
        $this->driver->executeScript('arguments[0].click()', [$element]);
        // A confirm dialog must be accepted by the test before any further WebDriver action.
        return $this->step();
    }
    public function click($selector = null)
    {
        if ($selector === null) { parent::click(); return $this->step(); }
        $element = $this->resolver->findOrFail($selector);
        if (! $element->isDisplayed() || ! $element->isEnabled()) throw new \RuntimeException('Elemen tidak dapat diklik: '.$selector);
        $this->driver->executeScript('arguments[0].scrollIntoView({block:"center"}); arguments[0].click()', [$element]);
        return $this->step();
    }
    public function clickLink($link, $element = 'a') { parent::clickLink($link, $element); return $this->step(); }
    public function check($field, $value = null)
    {
        $element = $this->resolver->resolveForChecking($field, $value);
        if (! $element->isSelected()) $this->clickCheckbox($element);
        return $this->step();
    }
    public function uncheck($field, $value = null)
    {
        $element = $this->resolver->resolveForChecking($field, $value);
        if ($element->isSelected()) $this->clickCheckbox($element);
        return $this->step();
    }
    private function clickCheckbox($element): void
    {
        if (! $element->isDisplayed() || ! $element->isEnabled()) throw new \RuntimeException('Checkbox tidak dapat diubah.');
        $this->driver->executeScript('arguments[0].scrollIntoView({block:"center"}); arguments[0].click()', [$element]);
    }
    public function select($field, $value = null) { parent::select($field, $value); return $this->step(); }
}
