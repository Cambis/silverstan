<?php

namespace Cambis\Silverstan\Tests\Type\Fixture;

use Cambis\Silverstan\Tests\Type\Source\MyPage;
use Cambis\Silverstan\Tests\Type\Source\MyPageController;
use Page;
use PageController;
use function PHPStan\Testing\assertType;

assertType(Page::class, Page::create());
assertType(PageController::class, PageController::create());
assertType(MyPage::class, MyPage::create());
assertType(MyPageController::class, MyPageController::create());
