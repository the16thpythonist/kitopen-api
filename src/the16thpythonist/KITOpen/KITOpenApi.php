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
        'author'        => '',
        'institute'     => '',
        'year'          => '',
        'tag'           => '',
        'type'          => '',
        'limit'         => 200,
        'offset'        => 0,
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
     * Changed 10.07.2018
     *
     * Fixed the bug, that I used the normal "$args" to get the values from and not "$this->>args" as I have loaded all
     * the values in there.
     * Moved the creation of the options array for the http client to a separate private method.
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
        $options = $this->assembleSearchOptions($args);

        // Sending the request
        $response = $this->client->get(self::SEARCH_URI, $options);

        // Getting the response
        $body = $response->getBody();
        $json = json_decode($body, true);

        // Formatting the response objects to publication objects
        $publications = array();
        foreach ($json as $response) {
            $publication = Publication::fromResponse($response);
            $publications[] = $publication;
        }

        return $publications;
    }

    /**
     * Creates the options array, that is to be passed to the http client which makes the API GET request.
     *
     * CHANGELOG
     *
     * Added 10.07.2018
     *
     * @since 0.0.0.1
     *
     * @param array $args
     * @return array
     */
    private function assembleSearchOptions(array $args) {
        /*
         * This array is the actual array, that is going to be used to create the URL parameters for the GET query.
         * Some of the values can be hardcoded, as they are not dependant on user preferences, ie given by the $args
         * array.
         */
        $query = array(
            'lang'                      => $this->config['lang'],
            'format'                    => 'csl_json',
            'style'                     => 'kit-3lines-title_b-authors-other',
            'referencing'               => 'all',
            'external_publications'     => 'all',
        );
        /*
         * This array is simply a mapping, as keys it has the strings that have to be used as the URL variables. These
         * are specified by the KIT Open API and cannot be changed. As values it has the corresponding keys used in
         * the $args array.
         * the array is being iterated to find out for which $args key there has actually been specified a value. If
         * there was no value specified for a key (which means default '') then the corresponding URL variable will not
         * appear in the URL as that would cause a HTTP Client exception. Only those args with actual values passed
         * are being added to the $query array to be used in the URL
         */
        $map = array(
            'organizations'             => 'institute',
            'authors'                   => 'author',
            'title_contains'            => 'tag',
            'year'                      => 'year',
            'limit'                     => 'limit',
            'offset'                    => 'offset',
            'types'                      => 'type'
        );
        foreach ($map as $option => $key) {
            if ($this->args[$key] !== '') {
                $query[$option] = $this->args[$key];
            }
        }
        $options = array('query' => $query);
        return $options;
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

    /**
     * returns the URL to a specific publication page, if given the id of that publication
     *
     * CHANGELOG
     *
     * Added 10.07.2018
     *
     * @since 0.0.0.2
     *
     * @param string $id    the kit open id of the publication for which to return the url
     * @return string   the url to the page of the publication
     */
    public static function getPublicationURL(string $id): string {
        /*
         * To access the specific page of any given KITOpen publication, the base url just has to be extended with the
         * id of that publication
         */
        return self::URI . $id;
    }
}