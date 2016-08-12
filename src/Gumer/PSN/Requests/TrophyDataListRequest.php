<?php namespace Gumer\PSN\Requests;

class TrophyDataListRequest extends AbstractAuthenticatedRequest {

	/**
	 * @var string
	 */
	protected $uri = 'https://{{region}}-tpy.np.community.playstation.net/trophy/v1/trophyTitles/{{npCommunicationId}}/trophyGroups/{{groupId}}/trophies?fields=%40default,trophyRare,trophyEarnedRate&npLanguage={{lang}}';

	/**
	 * @param array
	 */
	protected $params = array('lang' => 'en');

	/**
	 * @param string $communicationId
	 * @return void
	 */
	public function setCommunicationId($communicationId)
	{
		$this->params['npCommunicationId'] = (string) $communicationId;
	}

	/**
	 * @param string $groupId
	 * @return void
	 */
	public function setGroupId($groupId)
	{
		$this->params['groupId'] = (string) $groupId;
	}

	/**
	 * @param string $value
	 * @return void
	 */
	public function setLang($value)
	{
		$this->params['lang'] = mb_strtolower($value);
		$this->user()->setLang(mb_strtolower($value));
	}

}
