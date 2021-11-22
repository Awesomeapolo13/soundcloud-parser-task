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
     * @param EntityManagerInterface $em
     */
    public function __construct(ServiceEntityRepositoryInterface $repository, EntityManagerInterface $em)
    {
        $this->repository = $repository;
        $this->em = $em;
    }

    /**
     * @param string $url
     */
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
        // получаем массив треков автора, на пустоту дальше можно не проверять, т.к. есть проверка внутри метода loadAuthorAndTracks()
        $tracksData = $this->loadAuthorAndTracks($authorId);
        // пробуем получить текущего автора
        $currentAuthor = $this->repository->findAuthorWithTracks($tracksData[0]->user->id);

        // если автор не найден, то создаем новый объект сущности, сохраняем его и все полученные треки
        // проверка на существование такого трека не требуется, т.к. если нет автора, то и треков его быть не должно
        if (empty($currentAuthor)) {
            $this->saveAuthorWithTracks($tracksData[0]->user, $tracksData);
            return;
        }

        // если такой автор уже существует, то фильтруем уникальные треки для сохранения
        $this->saveUniqueTracks($tracksData, $currentAuthor);
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

                return $authorId;
            }
        }

        throw new NotFoundAuthorIdException();
    }

    /**
     * Фильтрует треки и оставляет только те, что не сохранены в БД
     *
     * Поскольку мы имеем дело с двумя объектами решил использовать вложенный цикл
     *
     * @param iterable $tracks - полученная от ресурса коллекция треков
     * @param iterable $alreadySavedTracks - коллекция треков, уже сохраненная в БД
     * @return iterable - отфильтрованная коллекция треков
     */
    protected function filterUniqueTracks(iterable $tracks, iterable $alreadySavedTracks): iterable
    {
        foreach ($tracks as $key => $track) {
            foreach ($alreadySavedTracks as $authorTrack) {
                if ($track->id === $authorTrack->getResourceId()) {
                    unset($tracks[$key]);
                    break;
                }
            }
        }

        return $tracks;
    }

    /**
     * Сохраняет данные автора и треков в БД
     *
     * @param object $authorData - объект данных автора
     * @param iterable $tracksData - коллекция треков
     */
    protected function saveAuthorWithTracks(object $authorData, iterable $tracksData): void
    {
        $saveAuthorWithTracks = Author::create($authorData);
        $saveAuthorWithTracks->createTracks($tracksData);
        $this->em->persist($saveAuthorWithTracks);
        $this->em->flush();
    }

    /**
     * Сохраняет треки автора, если они ранее не были сохранены
     *
     * @param iterable $tracks
     * @param Author $author
     */
    protected function saveUniqueTracks(iterable $tracks, Author $author): void
    {
        $uniqueTracks = $this->filterUniqueTracks($tracks, $author->getTracks());
        if (!empty($uniqueTracks)) {
            foreach ($uniqueTracks as $track) {
                $this->em->persist(Track::create($track, $author));
            }
            $this->em->flush();
        }
    }
}
