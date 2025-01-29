<?php

namespace Cambis\Silverstan\Tests\Page\Fixture;

use Cambis\Silverstan\Tests\Page\Source\MyPage;
use Cambis\Silverstan\Tests\Page\Source\MyPageController;
use Page;
use PageController;
use function PHPStan\Testing\assertType;

assertType(Page::class, Page::create());
assertType(PageController::class, PageController::create());
assertType(MyPage::class, MyPage::create());
assertType(MyPageController::class, MyPageController::create());
