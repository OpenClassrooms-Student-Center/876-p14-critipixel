<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth;

use App\Model\Entity\User;
use App\Tests\Functional\FunctionalTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class RegisterTest extends FunctionalTestCase
{
    public function testThatRegistrationShouldSucceeded(): void
    {
        $this->get('/auth/register');
        self::assertResponseIsSuccessful();

        $this->submit('S\'inscrire', self::createFormData());
        self::assertResponseRedirects('/auth/login');

        $user = $this->getEntityManager()->getRepository(User::class)->findOneByEmail('user@email.com');

        $userPasswordHasher = $this->service(UserPasswordHasherInterface::class);

        self::assertNotNull($user);
        self::assertSame('username', $user->getUsername());
        self::assertSame('user@email.com', $user->getEmail());
        self::assertTrue($userPasswordHasher->isPasswordValid($user, 'SuperPassword123!'));
    }

    /**
     * @param array<string, string> $formData
     * @dataProvider provideInvalidFormData
     */
    public function testThatRegistrationShouldFailed(array $formData): void
    {
        $this->get('/auth/register');
        self::assertResponseIsSuccessful();

        $crawler = $this->submit('S\'inscrire', $formData);
        self::assertResponseIsUnprocessable();
    }

    /**
     * @return iterable<array{array<string, string>}>
     */
    public static function provideInvalidFormData(): iterable
    {
        yield 'empty username' => [self::createFormData(['register[username]' => ''])];
        yield 'non unique username' => [self::createFormData(['register[username]' => 'user+1'])];
        yield 'too long username' => [self::createFormData(['register[username]' => 'Lorem ipsum dolor sit amet orci aliquam'])];
        yield 'empty email' => [self::createFormData(['register[email]' => ''])];
        yield 'non unique email' => [self::createFormData(['register[email]' => 'user+1@email.com'])];
        yield 'invalid email' => [self::createFormData(['register[email]' => 'fail'])];
    }

    /**
     * @param array<string, string> $overrideData
     * @return array<string, string>
     */
    public static function createFormData(array $overrideData = []): array
    {
        return $overrideData + [
            'register[username]' => 'username',
            'register[email]' => 'user@email.com',
            'register[plainPassword]' => 'SuperPassword123!'
        ];
    }
}
