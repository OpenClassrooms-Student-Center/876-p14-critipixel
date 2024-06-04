<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use App\Tests\Functional\FunctionalTestCase;

final class FilterTest extends FunctionalTestCase
{
    /**
     * @return iterable<string, array{
     *     query: array<string, mixed>,
     *     expectedCount: int,
     *     expectedOffsetFrom: int,
     *     expectedOffsetTo: int,
     *     expectedTotal: int,
     *     expectedPage: ?int,
     *     expectedPaginationLinks: string[],
     *     expectedVideoGames: string[]
     * }>
     */
    public static function provideUseCases(): iterable
    {
        yield 'First page' => self::createUseCase();
        yield 'Page #2' => self::createUseCase(
            query: ['page' => 2],
            expectedOffsetFrom: 11,
            expectedOffsetTo: 20,
            expectedPage: 2,
            expectedPaginationLinks: ['1', '2', '3', '4', '5'],
        );
        yield 'Last page' => self::createUseCase(
            query: ['page' => 5],
            expectedOffsetFrom: 41,
            expectedOffsetTo: 50,
            expectedPage: 5,
            expectedPaginationLinks: ['2', '3', '4', '5'],
        );
        yield 'First page, limit at 25' => self::createUseCase(
            query: ['limit' => 25],
            expectedCount: 25,
            expectedOffsetTo: 25,
            expectedPaginationLinks: ['1', '2'],
        );
        yield 'First page, limit at 50' => self::createUseCase(
            query: ['limit' => 50],
            expectedCount: 50,
            expectedOffsetTo: 50,
            expectedPage: null,
        );
        yield 'First page, sorting by title' => self::createUseCase(
            query: ['sorting' => 'Title'],
            expectedVideoGames: [
                'Jeu vidéo 9',
                'Jeu vidéo 8',
                'Jeu vidéo 7',
                'Jeu vidéo 6',
                'Jeu vidéo 5',
                'Jeu vidéo 49',
                'Jeu vidéo 48',
                'Jeu vidéo 47',
                'Jeu vidéo 46',
                'Jeu vidéo 45',
            ]
        );
        yield 'First page, sorting by title, direction on ascending' => self::createUseCase(
            query: ['sorting' => 'Title', 'direction' => 'Ascending'],
            expectedVideoGames: [
                'Jeu vidéo 0',
                'Jeu vidéo 1',
                'Jeu vidéo 10',
                'Jeu vidéo 11',
                'Jeu vidéo 12',
                'Jeu vidéo 13',
                'Jeu vidéo 14',
                'Jeu vidéo 15',
                'Jeu vidéo 16',
                'Jeu vidéo 17',
            ]
        );
        yield 'First page, filter by search' => self::createUseCase(
            query: ['filter' => ['search' => 'Jeu vidéo 49']],
            expectedCount: 1,
            expectedOffsetTo: 1,
            expectedTotal: 1,
            expectedPage: null,
            expectedVideoGames: ['Jeu vidéo 49']
        );
        yield 'First page, filter by 1 tag' => self::createUseCase(
            query: ['filter' => ['tags' => ['1']]],
            expectedTotal: 10,
            expectedPage: null,
            expectedVideoGames: [
                'Jeu vidéo 0',
                'Jeu vidéo 21',
                'Jeu vidéo 22',
                'Jeu vidéo 23',
                'Jeu vidéo 24',
                'Jeu vidéo 25',
                'Jeu vidéo 46',
                'Jeu vidéo 47',
                'Jeu vidéo 48',
                'Jeu vidéo 49',
            ]
        );
        yield 'First page, filter by 2 tags' => self::createUseCase(
            query: ['filter' => ['tags' => ['1', '2']]],
            expectedCount: 8,
            expectedOffsetTo: 8,
            expectedTotal: 8,
            expectedPage: null,
            expectedVideoGames: [
                'Jeu vidéo 0',
                'Jeu vidéo 22',
                'Jeu vidéo 23',
                'Jeu vidéo 24',
                'Jeu vidéo 25',
                'Jeu vidéo 47',
                'Jeu vidéo 48',
                'Jeu vidéo 49',
            ]
        );
        yield 'First page, filter by 3 tags' => self::createUseCase(
            query: ['filter' => ['tags' => ['1', '2', '3']]],
            expectedCount: 6,
            expectedOffsetTo: 6,
            expectedTotal: 6,
            expectedPage: null,
            expectedVideoGames: [
                'Jeu vidéo 0',
                'Jeu vidéo 23',
                'Jeu vidéo 24',
                'Jeu vidéo 25',
                'Jeu vidéo 48',
                'Jeu vidéo 49',
            ]
        );
        yield 'First page, filter by 4 tags' => self::createUseCase(
            query: ['filter' => ['tags' => ['1', '2', '3', '4']]],
            expectedCount: 4,
            expectedOffsetTo: 4,
            expectedTotal: 4,
            expectedPage: null,
            expectedVideoGames: [
                'Jeu vidéo 0',
                'Jeu vidéo 24',
                'Jeu vidéo 25',
                'Jeu vidéo 49',
            ]
        );
        yield 'First page, filter by 5 tags' => self::createUseCase(
            query: ['filter' => ['tags' => ['1', '2', '3', '4', '5']]],
            expectedCount: 2,
            expectedOffsetTo: 2,
            expectedTotal: 2,
            expectedPage: null,
            expectedVideoGames: [
                'Jeu vidéo 0',
                'Jeu vidéo 25',
            ]
        );
    }

    /**
     * @param array<string, mixed> $query
     * @param string[] $expectedPaginationLinks
     * @param string[] $expectedVideoGames
     * @dataProvider provideUseCases
     */
    public function testShouldShowVideoGamesByUseCase(
        array $query,
        int $expectedCount,
        int $expectedOffsetFrom,
        int $expectedOffsetTo,
        int $expectedTotal,
        ?int $expectedPage,
        array $expectedPaginationLinks,
        array $expectedVideoGames
    ): void {
        $this->get('/', $query);
        self::assertResponseIsSuccessful();
        self::assertSelectorCount($expectedCount, 'article.game-card');
        self::assertSelectorTextSame(
            'div.list-info',
            sprintf(
                'Affiche %d jeux vidéo de %d à %d sur les %d jeux vidéo',
                $expectedCount,
                $expectedOffsetFrom,
                $expectedOffsetTo,
                $expectedTotal
            )
        );
        if ($expectedPage === null) {
            self::assertSelectorNotExists('nav[aria-label="Pagination"]');
        } else {
            self::assertSelectorTextSame('li.page-item.active', (string) $expectedPage);
            self::assertSelectorCount(count($expectedPaginationLinks), 'li.page-item');
            foreach ($expectedPaginationLinks as $expectedPaginationLink) {
                self::assertSelectorExists(sprintf('li.page-item[aria-label="%s"]', $expectedPaginationLink));
            }
        }
        foreach (array_values($expectedVideoGames) as $index => $expectedVideoGame) {
            self::assertSelectorTextSame(
                sprintf('article.game-card:nth-child(%d) h2.game-card-title a', $index + 1),
                $expectedVideoGame
            );
        }
    }

    public function testShouldSortVideoGames(): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');
        self::assertSelectorTextSame(
            'article.game-card:nth-child(1) h2.game-card-title a',
            'Jeu vidéo 0'
        );
        self::assertSelectorTextSame(
            'article.game-card:nth-child(10) h2.game-card-title a',
            'Jeu vidéo 9'
        );

        $this->submit('Trier', ['limit' => 25,'sorting' => 'Title', 'direction' => 'Ascending'], 'GET');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(25, 'article.game-card');
        self::assertSelectorTextSame(
            'article.game-card:nth-child(1) h2.game-card-title a',
            'Jeu vidéo 0'
        );
        self::assertSelectorTextSame(
            'article.game-card:nth-child(25) h2.game-card-title a',
            'Jeu vidéo 30'
        );
    }

    public function testShouldFilterBySearchVideoGames(): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');
        self::assertSelectorTextSame(
            'article.game-card:nth-child(1) h2.game-card-title a',
            'Jeu vidéo 0'
        );
        self::assertSelectorTextSame(
            'article.game-card:nth-child(10) h2.game-card-title a',
            'Jeu vidéo 9'
        );

        $this->submit('Filtrer', ['filter[search]' => 'Jeu vidéo 49'], 'GET');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(1, 'article.game-card');
        self::assertSelectorTextSame(
            'article.game-card:nth-child(1) h2.game-card-title a',
            'Jeu vidéo 49'
        );
    }

    public function testShouldFilterByTagsVideoGames(): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');
        self::assertSelectorTextSame(
            'article.game-card:nth-child(1) h2.game-card-title a',
            'Jeu vidéo 0'
        );
        self::assertSelectorTextSame(
            'article.game-card:nth-child(10) h2.game-card-title a',
            'Jeu vidéo 9'
        );

        $this->submit(
            'Filtrer',
            [
                'filter[tags][0]' => '1',
                'filter[tags][1]' => '2',
                'filter[tags][2]' => '3',
                'filter[tags][3]' => '4',
                'filter[tags][4]' => '5',
            ],
            'GET'
        );
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(2, 'article.game-card');
        self::assertSelectorTextSame(
            'article.game-card:nth-child(1) h2.game-card-title a',
            'Jeu vidéo 0'
        );
        self::assertSelectorTextSame(
            'article.game-card:nth-child(2) h2.game-card-title a',
            'Jeu vidéo 25'
        );
    }

    /**
     * @param array<string, mixed> $query
     * @param int $expectedCount
     * @param int $expectedOffsetFrom
     * @param int $expectedOffsetTo
     * @param int $expectedTotal
     * @param ?int $expectedPage
     * @param null|string[] $expectedPaginationLinks
     * @param null|string[] $expectedVideoGames
     * @return array{
     *     query: array<string, mixed>,
     *     expectedCount: int,
     *      expectedOffsetFrom: int,
     *     expectedOffsetTo: int,
     *     expectedTotal: int,
     *     expectedPage: int|null,
     *     expectedPaginationLinks: string[],
     *     expectedVideoGames: string[]
     * }
     */
    private static function createUseCase(
        array $query = [],
        int $expectedCount = 10,
        int $expectedOffsetFrom = 1,
        int $expectedOffsetTo = 10,
        int $expectedTotal = 50,
        ?int $expectedPage = 1,
        ?array $expectedPaginationLinks = null,
        ?array $expectedVideoGames = null
    ): array {
        if ($expectedPage !== null) {
            $expectedPaginationLinks = $expectedPaginationLinks ?? [
                '1',
                '2',
                '3',
                '4',
            ];

            if ($expectedPage > 1) {
                $expectedPaginationLinks = array_merge(['Première page', 'Précédent'], $expectedPaginationLinks);
            }

            if ($expectedPage < ceil($expectedTotal / $expectedCount)) {
                $expectedPaginationLinks = array_merge($expectedPaginationLinks, ['Suivant', 'Dernière page']);
            }
        }

        return [
            'query' => $query,
            'expectedCount' => $expectedCount,
            'expectedOffsetFrom' => $expectedOffsetFrom,
            'expectedOffsetTo' => $expectedOffsetTo,
            'expectedTotal' => $expectedTotal,
            'expectedPage' => $expectedPage,
            'expectedPaginationLinks' => $expectedPaginationLinks ?? [],
            'expectedVideoGames' => $expectedVideoGames ?? array_fill_callback(
                    $expectedOffsetFrom - 1,
                    $expectedCount,
                    static fn (int $index) => sprintf(
                        'Jeu vidéo %d',
                        $index
                    )
                )
        ];
    }
}
