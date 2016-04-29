<?php namespace Gumer\PSN\Requests;

class TrophyInfoRequest extends AbstractAuthenticatedRequest {

	/**
	 * @var string
	 */
	protected $uri = 'https://{{region}}-tpy.np.community.playstation.net/trophy/v1/trophyTitles/{{npCommunicationId}}/trophyGroups/{{groupId}}/trophies/{{trophyID}}?fields=%40default,trophyRare,trophyEarnedRate&npLanguage={{lang}}';

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
	 * @param string $trophyId
	 * @return void
	 */
	public function setTrophyId($trophyId)
	{
		$this->params['trophyID'] = (string) $trophyId;
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
