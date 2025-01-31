<?php

declare(strict_types=1);

namespace App\Article\Http\Admin;

use App\Article\Domain\Article;
use App\Article\Domain\Articles;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/admin/article/list', methods: ['GET'])]
#[AsController]
final class ListAction
{
    public function __construct(private readonly Articles $articles)
    {
    }

    /**
     * @return Article[]
     */
    public function __invoke(): array
    {
        return $this->articles->getAll();
    }
}
