<?php
declare(strict_types=1);

/**
 * OpenAgenda API client.
 * Copyright (c) Erwane BRETON
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Erwane BRETON
 * @see         https://github.com/Erwane/openagenda-api
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
namespace OpenAgenda\Test\TestCase\Entity;

use Cake\Chronos\Chronos;
use OpenAgenda\Entity\Agenda;
use OpenAgenda\Entity\Entity;
use OpenAgenda\Entity\Event;
use OpenAgenda\Entity\Location;
use OpenAgenda\OpenAgenda;
use OpenAgenda\Test\test_app\TestApp\Entity as ent;
use PHPUnit\Framework\TestCase;

/**
 * Entity\Entity tests
 *
 * @uses   \OpenAgenda\Entity\Entity
 * @covers \OpenAgenda\Entity\Entity
 */
class EntityTest extends TestCase
{
    public function testConstructFromOpenAgenda()
    {
        $ent = new ent([
            'uid' => '1',
            'postalCode' => '12345',
            'createdAt' => Chronos::now()->toAtomString(),
            'description' => json_encode(['fr' => 'Lorem ipsum']),
            'state' => 1,
            'unknownField' => 'value',
            'agenda' => ['uid' => 123],
            'location' => new Location(['agendaUid' => 123, 'uid' => 456]),
            'event' => ['agendaUid' => 123, 'uid' => 789],
        ]);

        $this->assertEquals([
            'uid' => 1,
            'postalCode' => '12345',
            'createdAt' => Chronos::parse('2024-12-23T12:34:56+00:00'),
            'state' => true,
            'description' => ['fr' => 'Lorem ipsum'],
            'agenda' => new Agenda(['uid' => 123]),
            'location' => new Location(['agendaUid' => 123, 'uid' => 456]),
            'event' => new Event(['agendaUid' => 123, 'uid' => 789]),
        ], $ent->toArray());

        // boolean field
        $this->assertTrue($ent->state);

        // Unknown field exists in fields / property
        $this->assertEquals('value', $ent->unknownField);
    }

    public function testConstructNoSetter()
    {
        $ent = new ent(['uid' => '1'], ['useSetters' => false]);
        $this->assertSame('1', $ent->uid);
        $this->assertTrue($ent->isDirty());
        $this->assertTrue($ent->isNew());
    }

    public function testConstructNoSetterAndClean()
    {
        $ent = new ent(['uid' => '1'], ['markClean' => true, 'useSetters' => false]);
        $this->assertSame('1', $ent->uid);
        $this->assertFalse($ent->isDirty());
    }

    public function testSetter()
    {
        /** @uses \OpenAgenda\Entity\Entity::_setUid() */
        $ent = new ent(['uid' => '1']);
        $this->assertSame(1, $ent->uid);
    }

    public function testSetProperty()
    {
        $ent = new Ent();
        $ent->uid = '1';
        $this->assertSame(1, $ent->uid);
        $this->assertTrue($ent->has('uid'));
    }

    public function testSetEmpty(): void
    {
        $ent = new Ent();
        $this->expectException(\InvalidArgumentException::class);
        $ent->set(null, '');
    }

    public function testGetEmpty(): void
    {
        $ent = new Ent();
        $this->expectException(\InvalidArgumentException::class);
        $ent->get('');
    }

    public function testToOpenAgenda()
    {
        $now = Chronos::now();
        $ent = new ent([
            'uid' => 1,
            'postalCode' => '12345',
            'createdAt' => $now,
            'description' => ['fr' => 'Lorem ipsum'],
            'state' => true,
            'unknownField' => 'value',
        ]);

        $this->assertSame([
            'uid' => 1,
            'postalCode' => '12345',
            'createdAt' => '2024-12-23T12:34:56',
            'description' => ['fr' => 'Lorem ipsum'],
            'state' => 1,
        ], $ent->toOpenAgenda());
    }

    public function testToOpenAgendaChanged(): void
    {
        $ent = new ent([
            'uid' => 1,
            'postalCode' => '12345',
            'state' => true,
        ], ['markClean' => true]);

        $ent->set('state', false);

        $this->assertSame([
            'uid' => 1,
            'state' => 0,
        ], $ent->toOpenAgenda(true));
    }

    public function testArrayAccess()
    {
        $ent = new ent();

        $this->assertFalse(isset($ent['offsetExists']));
        $this->assertNull($ent['offsetGet']);
        $ent['offsetSet'] = 'value';
        $this->assertSame('value', $ent['offsetSet']);
        unset($ent['offsetSet']);
        $this->assertNull($ent['offsetSet']);
    }

    public function testSetNew(): void
    {
        $ent = new ent(['uid' => 1], ['markClean' => true]);
        $this->assertFalse($ent->isDirty('uid'));
        $ent->setNew(true);
        $this->assertTrue($ent->isDirty('uid'));
    }

    public function testIsDirty(): void
    {
        $ent = new ent([
            'id' => 99,
            'description' => ['fr' => 'lorem'],
        ], ['markClean' => true]);

        $ent->id = 99;
        $ent->description = ['fr' => 'lorem ipsum'];
        $this->assertTrue($ent->isDirty());
    }

    public function testDirtyNotSetWhenNoDiff()
    {
        $ent = new ent([
            'id' => 99,
            'description' => ['fr' => 'lorem'],
        ], ['markClean' => true]);
        $this->assertFalse($ent->isDirty());
        $ent->id = 99;
        $ent->description = ['fr' => 'lorem'];
        $this->assertFalse($ent->isDirty());
    }

    public static function dataNoHtml(): array
    {
        return [
            ['simple string', true, 'simple string'],
            ["new\n  \nline", true, "new\n \nline"],
            ["new\n\nlines\n", false, 'new lines'],
            ['ça d&eacute;veloppe', true, 'ça développe'],
            ['<span>this</span> <a href="not this">is a</a>  weird text', true, 'this is a weird text'],
            [
                <<<HTML
<span>This</span> description
<p>should be on <a href="not this">one</a></p>
<ul>
<li>line </li>
<li>and clean.</li>
</ul>
HTML
                , false, 'This description should be on one line and clean.',
            ],
        ];
    }

    /** @dataProvider dataNoHtml */
    public function testNoHtml($html, $keep, $expected): void
    {
        $result = Entity::noHtml($html, $keep);
        $this->assertSame($expected, $result);
    }

    public static function dataCleanupHtml(): array
    {
        return [
            [
                <<<HTML
<!-- Those are allowed -->
<a href="/internal-link.html">internal link</a>
<a href="https://my-domain.com/internal-link.html" target="_self">internal link</a>
<a href="https://example.com/external-link.html" target="nothis" rel="noopener">external link</a>
<b>bold</b>
<strong>strong</strong>
<i>i</i>
<em>em</em>
<u>u</u>
<p>p</p>
<img src="https://example.com/image.jpg" alt="image" width="10" height="10" data-test="Value">
<hr>
<span style="color:red">span</span>
<ul><li>uli</li></ul>
<ol><li>oli</li></ol>
<h1>h1</h1>
<h2>h2</h2>
<h3>h3</h3>
<h4>h4</h4>
<h5>h5</h5>
<!-- Those are disallowed -->
<div>div</div>
<header>header</header>
<section>section</section>
<article>article</article>
<main>main</main>
<pre>pre</pre>
<code>code</code>
HTML
                , null,
                <<<HTML
<a href="/internal-link.html">internal link</a>
<a href="https://my-domain.com/internal-link.html" target="_blank" rel="noreferrer noopener">internal link</a>
<a href="https://example.com/external-link.html" target="_blank" rel="noreferrer noopener">external link</a>
<b>bold</b>
<strong>strong</strong>
<i>i</i>
<em>em</em>
<u>u</u>
<p>p</p>
<img src="https://example.com/image.jpg" alt="image" width="10" height="10" />
<hr />
span
<ul><li>uli</li></ul>
<ol><li>oli</li></ol>
<h3>h1</h3>
<h3>h2</h3>
<h3>h3</h3>
<h4>h4</h4>
<h5>h5</h5>

div
header
section
article
main
pre
code
HTML
                ,
            ],
            [
                <<<HTML
<a href="/internal-link.html">internal link</a>
<a href="https://my-domain.com/internal-link.html">internal link</a>
<a href="https://example.com/external-link.html">external link</a>
<img src="/image.jpg" alt="image">
<img src="https://example.com/image.jpg" alt="image">
HTML
                , 'https://my-domain.com',
                <<<HTML
<a href="https://my-domain.com/internal-link.html">internal link</a>
<a href="https://my-domain.com/internal-link.html">internal link</a>
<a href="https://example.com/external-link.html" target="_blank" rel="noreferrer noopener">external link</a>
<img src="https://my-domain.com/image.jpg" alt="image" />
<img src="https://example.com/image.jpg" alt="image" />
HTML
                ,

            ],
        ];
    }

    /** @dataProvider dataCleanupHtml */
    public function testCleanupHtml($html, $baseUrl, $expected): void
    {
        OpenAgenda::setProjectUrl($baseUrl);
        $result = Entity::cleanupHtml($html);
        $this->assertSame($expected, $result);
    }

    public static function dataHtmlToMarkdown(): array
    {
        return [
            [
                'no html',
                'no html',
            ],
            [
                '<h3>title</h3><p>Hello</p>',
                '### title

Hello',
            ],
        ];
    }

    /** @dataProvider dataHtmlToMarkdown */
    public function testHtmlToMarkdown($html, $expected): void
    {
        $result = Entity::htmlToMarkdown($html);
        $this->assertSame($expected, $result);
    }
}
