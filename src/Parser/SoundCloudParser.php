<?php

namespace App\Parser;

use App\Entity\Author;
use App\Entity\Track;
use App\Exception\NotFoundAuthorIdException;
use DiDom\Document;
use App\Exception\HttpRequestException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class SoundCloudParser implements ParserInterface
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var ServiceEntityRepositoryInterface
     */
    private $repository;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @param ServiceEntityRepositoryInterface $repository
     */
    public function __construct(ServiceEntityRepositoryInterface $repository, EntityManagerInterface $em)
    {
        $this->repository = $repository;
        $this->em = $em;
    }


    public function setUrl(string $url)
    {
        $this->url = $url;
    }

    public function parse()
    {
        // загружаем страницу по url
        $document = new Document($this->url, true);
        // получаем id автора
        $authorId = $this->findAuthorIdFromHtml($document->find('meta'));
        // получаем массив треков автора, на пустоту дальше можно не проверять, т.к. есть проверка внутри метода
        $tracksData = $this->loadAuthorAndTracks($authorId);

        $tracksToSave = [];

        foreach ($tracksData as $track) {
            $tracksToSave[] = [
                'title' => $track->title,
                'duration' => $track->full_duration, // продолжительность
                'playbackCount' => $track->playback_count, // количество прослушиваний
                'commentsCount' => $track->comment_count, // количество комментариев
            ];
        }

        $authorData = [
            'id' => $tracksData[0]->user->id,
            'name' => $tracksData[0]->user->username,
            'alias' => $tracksData[0]->user->full_name,
            'city' => $tracksData[0]->user->city,
            'followersCount' => $tracksData[0]->user->followers_count,
        ];

        $saveAuthorWithTracks = Author::create($tracksData[0]->user);

        $saveAuthorWithTracks->createTracks($tracksData);

        $this->em->persist($saveAuthorWithTracks);
        $this->em->flush();

        dump($authorData, $tracksToSave);
    }

    /**
     * Совершает запрос к API SoundCloud и возвращает массив с информацией о треках
     *
     * @param int $authorId - id автора
     * @return array - массив треков
     * @throws HttpRequestException - в случае неудавшегося запроса
     */
    protected function loadAuthorAndTracks(int $authorId): array
    {
        $ch = curl_init(); // инициализация

        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://api-v2.soundcloud.com/users/' . $authorId . '/tracks?representation=&client_id=aruu5nVXiDILh6Dg7IlLpyhpjsnC2POa&limit=20&offset=0&linked_partitioning=1&app_version=1637227382&app_locale=en',
            CURLOPT_RETURNTRANSFER => true, // устанавливаем, чтобы запрос вернул false в случае неудачи
            CURLOPT_CONNECTTIMEOUT => 30,
        ]);

        $tracksData = curl_exec($ch);
        curl_close($ch); // закрываем соединение

        if (!$tracksData || empty(json_decode($tracksData)->collection)) {
            throw new HttpRequestException('Не удалось получить данные при выполнении запроса');
        }

        return json_decode($tracksData)->collection;
    }

    /**
     * Находит id автора в переданной коллекции тегов
     *
     * Получает коллекцию из тегов, в которых может содержаться id автора.
     * Сейчас анализирует метатеги, т.к. id автора найден именно в них.
     * При изменении в структуре верстки, можно будет переопределить метод.
     *
     * @param iterable $tagsArray - коллекция или массив тегов
     * @return int - id автора
     * @throws NotFoundAuthorIdException - выбрасываем в случае, если id автора не удалось найти
     */
    protected function findAuthorIdFromHtml(iterable $tagsArray): int
    {
        foreach ($tagsArray as $tag) {
            if (!empty($tag->getAttribute('content'))) {
                $authorId = !empty(explode(':', $tag->getAttribute('content'))[2]) ? explode(':', $tag->getAttribute('content'))[2] : null;
                if (empty($authorId)) {
                    continue;
                }
                dump($authorId);
                return $authorId;
            }
        }

        throw new NotFoundAuthorIdException();
    }
}
