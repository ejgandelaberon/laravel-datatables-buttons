<?php

namespace Yajra\DataTables\Buttons\Tests;

use PHPUnit\Framework\Attributes\Test;
use Yajra\DataTables\Html\DataTableHtml;

class DataTableHtmlTest extends TestCase
{
    #[Test]
    public function it_resolves_constructor_dependencies_through_the_container_without_explicit_arguments(): void
    {
        $builder = ContainerResolvedDataTableHtml::make();

        $this->assertSame('container-users', $builder->getTableId());
    }

    #[Test]
    public function it_resolves_the_static_class_with_positional_arguments_through_the_container(): void
    {
        $repository = app(DataTableHtmlUserRepository::class);
        $repository->tablePrefix = 'explicit-';

        $builder = ContainerResolvedDataTableHtml::make($repository, 'users');

        $this->assertSame('explicit-users', $builder->getTableId());
    }

    #[Test]
    public function it_passes_all_positional_arguments_to_a_variadic_constructor(): void
    {
        $builder = VariadicDataTableHtml::make('users', 'active');

        $this->assertSame('users-active', $builder->getTableId());
    }

    #[Test]
    public function it_maps_positional_arguments_to_the_bound_concrete_constructor(): void
    {
        app()->bind(ContainerResolvedDataTableHtml::class, BoundDataTableHtml::class);

        $repository = app(DataTableHtmlUserRepository::class);
        $repository->tablePrefix = 'explicit-';

        $builder = ContainerResolvedDataTableHtml::make($repository, 'users');

        $this->assertSame('bound-explicit-users', $builder->getTableId());
    }

    #[Test]
    public function it_runs_container_lifecycle_callbacks_before_handling(): void
    {
        app()->resolving(
            ContainerResolvedDataTableHtml::class,
            fn (ContainerResolvedDataTableHtml $html) => $html->prefixTableId('resolving-')
        );
        app()->afterResolving(
            ContainerResolvedDataTableHtml::class,
            fn (ContainerResolvedDataTableHtml $html) => $html->prefixTableId('resolved-')
        );

        $repository = app(DataTableHtmlUserRepository::class);
        $repository->tablePrefix = 'explicit-';

        $builder = ContainerResolvedDataTableHtml::make($repository, 'users');

        $this->assertSame('resolved-resolving-explicit-users', $builder->getTableId());
    }
}

class ContainerResolvedDataTableHtml extends DataTableHtml
{
    public function __construct(DataTableHtmlUserRepository $repository, string $tableId = 'users')
    {
        $this->tableId = $repository->tablePrefix.$tableId;
    }

    public function prefixTableId(string $prefix): void
    {
        $this->tableId = $prefix.$this->tableId;
    }
}

class DataTableHtmlUserRepository
{
    public string $tablePrefix = 'container-';
}

class VariadicDataTableHtml extends DataTableHtml
{
    public function __construct(string ...$tableIdParts)
    {
        $this->tableId = implode('-', $tableIdParts);
    }
}

class BoundDataTableHtml extends ContainerResolvedDataTableHtml
{
    public function __construct(DataTableHtmlUserRepository $dataSource, string $identifier = 'users')
    {
        $this->tableId = 'bound-'.$dataSource->tablePrefix.$identifier;
    }
}
