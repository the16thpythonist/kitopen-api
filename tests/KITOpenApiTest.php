<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 10.07.18
 * Time: 08:19
 */

use PHPUnit\Framework\TestCase;

use the16thpythonist\KITOpen\KITOpenApi;

class KITOpenApiTest extends TestCase
{
    /**
     * Tests if a very basic api call returns data at all
     *
     * @since 0.0.0.0
     */
    public function testDoesBasicallyWork() {
        $api = new KITOpenApi();
        $args = array(
            'type'          => 'ZEITSCHRIFTENAUFSATZ',
            'institute'     => 'IPE',
        );
        $publications = $api->search($args);
        $this->assertNotEquals(count($publications), 0);
    }
}