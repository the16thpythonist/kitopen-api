<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 03.07.18
 * Time: 07:26
 */

namespace the16thpythonist\KITOpen;

use GuzzleHttp\Client;
use InvalidArgumentException;

/**
 * Class KITOpenApi
 *
 * @since 0.0.0.0
 *
 * @package the16thpythonist\KITOpen
 */
class KITOpenApi
{
    const URI = "https://https://publikationen.bibliothek.kit.edu/";
    const SEARCH_URI = "https://publikationen.bibliothek.kit.edu/publikationslisten/get.php";
    const TIMEOUT = 30;

    const CONFIG_DEFAULT = array(
        'lang'      => 'en',
        'timeout'   => 30
    );
    const ARGS_DEFAULT = array(

    );

    /**
     * @var array $config the array, which contains the configuration for the api object. not state dependant
     */
    private $config;
    /**
     * @var Client $client the http client used for getting the response from the server
     */
    private $client;
    /**
     * @var array $args this attribute will later contain the search arguments array of the CURRENT search, which means
     * it is being set as soon as a new search method call is invoked
     */
    private $args;

    /**
     * KITOpenApi constructor.
     *
     * The constructor has an optional array argument, which can contain the configuration for the API.
     * This array can contain the following keys:
     * - lang       : The language of the response, can be german (de) or english (en)
     * - timeout    : The timeout in seconds for the http client
     *
     * CHANGELOG
     *
     * Added 03.07.2018
     *
     * @since 0.0.0.0
     *
     * @param array $config
     */
    public function __construct(array $config=self::CONFIG_DEFAULT)
    {
        // Extracting the data from the config dict
        $this->config = self::CONFIG_DEFAULT;
        $this->config = array_replace($this->config, $config);

        $this->client = new Client(array(
            'timeout' => $this->config['timeout']
        ));
    }

    /**
     * Sends a search request to the KITOpen Api with the given search arguments
     *
     * The possible arguments for the $args array:
     * - author         : The names of authors
     * - institute   :
     * - year
     * - tag
     * - type
     * - limit
     * - offset
     *
     * CHANGELOG
     *
     * Added 03.07.2018
     *
     * @since 0.0.0.0
     *
     * @throws InvalidArgumentException if there is no search parameter given for a search
     *
     * @param array $args
     *
     * @return array
     */
    public function search(array $args) {
        // Checking for validity first: If the args array does not contain a single item, that specifies what to search
        // for there cannot be a search
        $this->checkSearchArgs($args);


        $this->args = self::ARGS_DEFAULT;
        $this->args = array_replace($this->args, $args);

        // Building the array to be passed as URL parameters to the GET request
        $options = array(
            'query' => array(
                'lang'                      => $this->config['lang'],
                'format'                    => 'csl_json',
                'style'                     => 'kit-3lines-title_b-authors-other',
                'referencing'               => 'all',
                'external_publications'     => 'all',
                'organisations'             => $args['institute'],
                'authors'                   => $args['author'],
                'title_contains'            => $args['tag'],
                'year'                      => $args['year'],
                'limit'                     => $args['limit'],
                'offset'                    => $args['offset']
            )
        );

        // Sending the request
        $response = $this->client->get(self::SEARCH_URI, $options);

        // Getting the response
        $body = $response->getBody();
        $json = json_decode($body, true);

        // Formatting the response objects to publication objects
        $publications = array();
        foreach ($json as $reponse) {
            $publication = Publication::fromResponse($response);
            $publications[] = $publication;
        }

        return $publications;
    }

    /**
     * Checks the arguments of a search, before the search request is being sent to the API, to prevent common mistakes
     *
     * CHANGELOG
     *
     * Added 04.07.2018
     *
     * @since 0.0.0.0
     *
     * @throws InvalidArgumentException if there is no search parameter given for a search
     *
     * @param array $args the array of arguments passed to the search method
     */
    private function checkSearchArgs(array $args) {

        // Checking if the args actually contain parameters for a valid search
        $contains_author = array_key_exists('author', $args);
        $contains_tag = array_key_exists('tag', $args);
        $contains_organisation = array_key_exists('institute', $args);
        if(! ($contains_tag || $contains_organisation || $contains_author) ) {
            throw new InvalidArgumentException('There was no search parameter specified for the KITOpen Search');
        }


    }

}