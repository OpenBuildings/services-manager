<?php

/**
 * @group   somegroup
 */
class Service_SentryTest extends Testcase_Extended {

	public function test_capture_exception()
	{
		$sentry = $this->getMock('Service_Sentry', array('initialized', 'send_exception_with_user_data'), array('sentry'));
		$user = Jam::find('user', 1);

		$exception = new Exception('test');

		$data = array('data' => 'testdata');

		$sentry
			->expects($this->exactly(3))
			->method('initialized')
			->will($this->onConsecutiveCalls(FALSE, TRUE, TRUE));

		$sentry
			->expects($this->at(2))
			->method('send_exception_with_user_data')
			->with($this->identicalTo($exception), $this->equalTo(NULL), $this->equalTo(NULL), $this->equalTo(array()))
			->will($this->returnValue('test_id_2'));

		$sentry
			->expects($this->at(4))
			->method('send_exception_with_user_data')
			->with($this->identicalTo($exception), $this->equalTo($user->id()), $this->equalTo($user->email), $this->equalTo(array()))
			->will($this->returnValue('test_id_3'));


		$result_1 = $sentry
			->capture_exception($exception);

		$this->assertNull($result_1);

		$result_2 = $sentry
			->capture_exception($exception);

		$this->assertEquals('test_id_2', $result_2);

		Auth::instance()->force_login($user);

		$result_3 = $sentry
			->capture_exception($exception);

		$this->assertEquals('test_id_3', $result_3);		

	}
}
