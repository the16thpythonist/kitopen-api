<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 04.07.18
 * Time: 11:14
 */

namespace the16thpythonist\KITOpen;


/**
 * Class Author
 *
 * An Author object represents the data structure returned by the KITOpen API to represent the author of a publication.
 *
 * @package the16thpythonist\KITOpen
 *
 * @since 0.0.0.0
 */
class Author
{
    const DEFAULT = array(
        'first'     => '',
        'last'      => ''
    );

    /**
     * @var array $data the array, that actually contains all the 'attributes' of the Author object, which includes
     *                  the first name and the last name of the author
     */
    public $data;

    /**
     * Author constructor.
     *
     * CHANGELOG
     *
     * Added 04.07.2018
     *
     * @since 0.0.0.0
     *
     * @param $args
     */
    public function __construct($args)
    {
        $this->data = self::DEFAULT;
        $this->data = array_replace($this->data, $args);
    }

    /**
     * returns the first name of the author
     *
     * CHANGELOG
     *
     * Added 04.07.2018
     *
     * @since 0.0.0.0
     *
     * @return string
     */
    public function getFirstName() : string {
        return $this->data['first'];
    }

    /**
     * returns the last name of the author
     *
     * CHANGELOG
     *
     * Added 04.07.2018
     *
     * @since 0.0.0.0
     *
     * @return string
     */
    public function getLastName() : string {
        return $this->data['last'];
    }

    /**
     * returns the indexed name format for that author
     *
     * The indexed name format consists of the last name a comma and the first letter of the first name like this:
     * Lastname, F.
     *
     * Example
     * for the author "John Doe":
     * Doe, J.
     *
     * CHANGELOG
     *
     * Added 04.07.2018
     *
     * @since 0.0.0.0
     *
     * @return string
     */
    public function getIndexedName() : string {
        return $this->getLastName() . ', ' . $this->getFirstName()[0];
    }

    /**
     * Creates an Author object from the data structure given in the response of the KITOpen GET request
     *
     * In the response to a KITOpen get request
     *
     * @since 0.0.0.0
     *
     * @param $response
     * @return Author
     */
    public static function fromResponse($response) : Author {
        $args = array(
            'first'     => $response['given'] ?? '',
            'last'      => $response['family'] ?? '',
        );

        return new Author($args);
    }
}