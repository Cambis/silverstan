<?php

namespace Cambis\Silverstan\Tests\Extension\Reflection\Fixture;

use SilverStripe\Assets\Image;
use SilverStripe\Assets\Storage\DBFile;
use function PHPStan\Testing\assertType;

$foo = Image::create();
$bar = DBFile::create();

assertType('SilverStripe\ORM\FieldType\DBHTMLText', $foo->ResponsiveSet1());
assertType('SilverStripe\ORM\FieldType\DBHTMLText', $bar->ResponsiveSet1());
