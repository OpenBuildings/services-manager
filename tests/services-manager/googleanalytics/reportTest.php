<?php

/**
 * Tests for briefs controller
 * @group service
 * @group service.ga
 * @package Stat
 */
class GoogleAnalytics_ReportTest extends Unittest_TestCase
{
	public $report;

	public function setUp()
	{
		parent::setUp();
		$valid_data = array(
			'kind' => 'analytics#gaData',
			'id' => 'https://www.googleapis.com/analytics/v3/data/ga?ids=ga:54199329&metrics=ga:visits&start-date=2012-09-15&end-date=2012-10-15&start-index=1&max-results=10',
			'query' => array(
				'start-date' => '2012-09-15',
				'end-date' => '2012-10-15',
				'ids' => 'ga:54199329',
				'metrics' => array('0' => 'ga:visits'),
				'start-index' => '1',
				'max-results' => '10',
			),
			'itemsPerPage' => '10',
			'totalResults' => '1',
			'selfLink' => 'https://www.googleapis.com/analytics/v3/data/ga?ids=ga:54199329&metrics=ga:visits&start-date=2012-09-15&end-date=2012-10-15&start-index=1&max-results=10',
			'profileInfo' => array(
				'profileId' => '54199329',
				'accountId' => '4765782',
				'webPropertyId' => 'UA-4765782-20',
				'internalWebPropertyId' => '53352729',
				'profileName' => 'Clippings',
				'tableId' => 'ga:54199329',
			),
			'containsSampledData' => '',
			'columnHeaders' => array(array(
				'name' => 'ga:visits',
				'columnType' => 'METRIC',
				'dataType' => 'INTEGER',
			)),
			'totalsForAllResults' => array('ga:visits' => '20369'),
			'rows' => array(0 => array('20369')),
		);

		$this->report = $this->getMock('Service_GoogleAnalytics_Report', array('retrieve'), array('ga:54199329', 'ya29.AHES6ZTqsrcU7TfWCTwoc8II51FTD3tSfiwUXHhosWPK_uhfEQ'));
		$this->report
			->expects($this->any())
			->method('retrieve')
			->will($this->returnValue($valid_data));
	}

	public function test_getters_setters()
	{
		$report = Service_GoogleAnalytics_Report::factory('ga:54199329', 'ya29.AHES6ZTqsrcU7TfWCTwoc8II51FTD3tSfiwUXHhosWPK_uhfEQ');

		$this->assertEquals('ga:54199329', $report->project_id());
		$this->assertEquals('ya29.AHES6ZTqsrcU7TfWCTwoc8II51FTD3tSfiwUXHhosWPK_uhfEQ', $report->access_token());

		$this->assertEquals(date('Y-m-d', strtotime('today')), $report->end_date());
		$this->assertEquals(date('Y-m-d', strtotime('1 month ago')), $report->start_date());

		$report
			->metrics('ga:entrances')
			->start_date('1 week ago')
			->end_date('yesterday')
			->dimensions('ga:landingPagePath')
			->max_results(5)
			->sort('-ga:visits');

		$expected_params = array(
			'ids' => 'ga:54199329',
			'access_token' => 'ya29.AHES6ZTqsrcU7TfWCTwoc8II51FTD3tSfiwUXHhosWPK_uhfEQ',
			'start-date' => date('Y-m-d', strtotime('1 week ago')),
			'end-date' => date('Y-m-d', strtotime('yesterday')),
			'metrics' => 'ga:entrances',
			'dimensions' => 'ga:landingPagePath',
			'max-results' => '5',
			'sort' => '-ga:visits',
		);

		$this->assertEquals($expected_params, $report->request_params());
	}

	public function test_rows()
	{
		$this->assertEquals(array(0 => array('20369')), $this->report->rows());
	}

	public function test_total()
	{
		$this->assertEquals('20369', $this->report->total());
	}
}

