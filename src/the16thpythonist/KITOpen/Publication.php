<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 04.07.18
 * Time: 10:50
 */

namespace the16thpythonist\KITOpen;

/**
 * Class Publication
 *
 * Publication objects represents the data returned for a publication, that has been returned by the KITOpen API
 *
 * @since 0.0.0.0
 *
 * @package the16thpythonist\KITOpen
 */
class Publication
{
    const URI = "https://https://publikationen.bibliothek.kit.edu/";

    const DEFAULT = array(
        'title'         => '',
        'journal'       => '',
        'volume'        => '',
        'doi'           => '',
        'issn'          => '',
        'isbn'          => '',
        'type'          => '',
        'id'            => '',
        'available'     => false,
        'authors'       => array()
    );

    public $data;

    /**
     * Publication constructor.
     *
     * The args array contains:
     * - title
     * - journal
     * - volume
     * - doi
     * - type
     * - issn
     * - isbn
     * - id
     * - available
     * - date
     * - authors
     *
     * CHANGELOG
     *
     * Added 04.07.2018
     *
     * @since 0.0.0.0
     *
     * @param $args
     */
    public function __construct(array $args)
    {
        $this->data = self::DEFAULT;
        $this->data = array_replace($this->data, $args);

    }

    /**
     * returns the title of the publication
     *
     * CHANGELOG
     *
     * Added 04.07.2018
     *
     * @since 0.0.0.0
     *
     * @return string
     */
    public function getTitle(): string {
        return $this->data['title'];
    }

    /**
     * returns the journal where the publication was published in
     *
     * CHANGELOG
     *
     * Added 04.07.2018
     *
     * @since 0.0.0.0
     *
     * @return string
     */
    public function getJournal(): string {
        return $this->data['journal'];
    }

    /**
     * returns the volume of the journal, where the publication was published
     *
     * CHANGELOG
     *
     * Added 04.07.2018
     *
     * @since 0.0.0.0
     *
     * @return string
     */
    public function getVolume(): string {
        return $this->data['volume'];
    }

    /**
     * returns the type of publication
     *
     * CHANGELOG
     *
     * Added 04.07.2018
     *
     * @since 0.0.0.0
     *
     * @return string
     */
    public function getType(): string {
        return $this->data['title'];
    }

    /**
     * returns the DOI of the publication
     *
     * CHANGELOG
     *
     * Added 04.07.2018
     *
     * @since 0.0.0.0
     *
     * @return string
     */
    public function getDOI(): string {
        return $this->data['doi'];
    }

    /**
     * returns the kit publication id for the publication. KIT internal id system
     *
     * CHANGELOG
     *
     * Added 04.07.2018
     *
     * @since 0.0.0.0
     *
     * @return string
     */
    public function getID(): string {
        return $this->data['id'];
    }

    /**
     * whether or not the full text is available on the kit open site
     *
     * CHANGELOG
     *
     * Added 04.07.2018
     *
     * @since 0.0.0.0
     *
     * @return bool
     */
    public function isFullTextAvailable(): bool {
        return $this->data['available'];
    }

    /**
     * returns the array of Author objects, that represent the authors the
     *
     * CHANGELOG
     *
     * Added 04.07.2018
     *
     * @since 0.0.0.0
     *
     * @return array
     */
    public function getAuthors(): array {
        return $this->data['authors'];
    }

    /**
     * returns the uri to the actual detailed page for the publication of the KIT library
     *
     * CHANGELOG
     *
     * Added 04.07.2018
     *
     * @since 0.0.0.0
     *
     * @return string
     */
    public function getURI(): string {
        return self::URI . $this->getID();
    }

    /**
     * Creates a new Publication object from the array structure that was in the response object of the KITOpen.
     *
     * CHANGELOG
     *
     * Added 04.07.2018
     *
     * @since 0.0.0.0
     *
     * @param array $response the assoc array that was from the API response
     * @return Publication
     */
    public static function fromResponse(array $response): Publication {
        $authors = array();
        foreach ($response['authors'] as $author_response) {
            $author = Author::fromResponse($author_response);
            $authors[] = $author;
        }

        $args = array(
            'title'                 => $response['title'],
            'journal'               => $response['container-title'] ?? '',
            'doi'                   => $response['DOI'] ?? '',
            'issn'                  => $response['ISSN'] ?? '',
            'isbn'                  => $response['ISBN'] ?? '',
            'type'                  => $response['type'] ?? '',
            'available'             => $response['kit-has-full-text'] ?? false,
            'id'                    => $response['kit-publication-id'] ?? '',
            'authors'               => $authors
        );

        return new Publication($args);
    }
}