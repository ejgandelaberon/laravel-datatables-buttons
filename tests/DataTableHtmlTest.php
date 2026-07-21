<?php

namespace Yajra\DataTables\Buttons\Tests;

use PHPUnit\Framework\Attributes\Test;
use Yajra\DataTables\Html\DataTableHtml;

class DataTableHtmlTest extends TestCase
{
    #[Test]
    public function it_resolves_the_static_class_with_positional_arguments_through_the_container(): void
    {
        $repository = app(DataTableHtmlUserRepository::class);
        $repository->tablePrefix = 'explicit-';

        $builder = ContainerResolvedDataTableHtml::make($repository, 'users');

        $this->assertSame('explicit-users', $builder->getTableId());
    }
}

class ContainerResolvedDataTableHtml extends DataTableHtml
{
    public function __construct(DataTableHtmlUserRepository $repository, string $tableId)
    {
        $this->tableId = $repository->tablePrefix.$tableId;
    }
}

class DataTableHtmlUserRepository
{
    public string $tablePrefix = 'container-';
}
