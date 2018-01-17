<?php
/*************************************************
 *
 * GchipsList - the PHP net client
 * Author: rong
 * Copyright (c): 2014-2014, all rights reserved
 * Version: 1.0.0
 * ��ץȡ�����ݽ��ж��β�ִ���
 *
 *************************************************/
class GchipsList{
	
	var $datahtml = "";//�ͺ����ݽ��
	var $onedisthtml = "";//һ�������̵����ݽ��
	
	function __construct()
	{
		$this->onedisthtml = "";
		$this->datahtml = "";
	}
	/*======================================================================*\
        Function:	getDistributor
        Purpose:	������
        Input:		������Dom
        Output:		�����Ľ��
    \*======================================================================*/
	public function getDistributor($distvalue)
	{
		preg_match('/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png))\"?.+>/i',$distvalue,$match);
		return str_replace('https://media.findchips.com/srp/logos/results/','',$match[1]);
	}
	public function getPDFurl($PDFurl)
	{
		$preg='/<a.*?href="(.*?)".*?>/is';
		
		preg_match_all($preg,$PDFurl,$match);//��$str������ƥ�����з���$preg����$match��
	
		echo $match[1][1];
	}
	/*======================================================================*\
        Function:	getSalePrice
        Purpose:	�������ۼ۸��б�
        Input:		$pricedoc:�۸� dom,$dsbtitle:�����̱���
					,$kubun:���� 0��Ԫԭ��,1��������Ҽ۸�
        Output:		���ս��
    \*======================================================================*/
	public function getMallSalePrice($pricedoc,$dsbtitle,$kubun,$mallPara)
	{
		$pricelist = "";//��ʾ�۸�
		$hdunitprice = "";//���ؼ۸���ϸ��������
		
	    //<li><span class="label">25000</span> <span class="value" data-baseprice="0.2429" data-basecurrency="USD">$0.2429</span></li>
		$onevalue = pq($pricedoc)->find('li');
		foreach($onevalue as $results)
		{
			$pricecnt = pq($results)->find('.label')->html();//����
			if($pricecnt == "")
			{
				$pricecnt = "1";
			}
			$untprice = pq($results)->find('.value')->html();//������Ӧ����
			//echo $untprice;
			$untprice = str_replace("¥","",$untprice);
			$untprice = str_replace("$","",$untprice);
			//echo $untprice;
			$pricelist = round($untprice*$mallPara,2);
			//echo $pricelist;
			//����Ҽ۸� element14 Asia-Pacific=e����		
			if($untprice != "0")
			{
				//$pricelist .= $this->setRmbPrice($pricecnt,$untprice,$kubun,$dsbtitle);
				break;
				//$pricelist .= "<span class='tx_l'>".$pricecnt ."</span><span>".$untprice ."</span>";
				//$hdunitprice .= str_replace(",","",$pricecnt)."|".$untprice.",";
			}			
		}
		//$pricedata = array($pricelist,$hdunitprice);
		//�������ս��
		return $pricelist;
	}
    /*======================================================================*\
        Function:	getSalePrice
        Purpose:	�������ۼ۸��б�
        Input:		$pricedoc:�۸� dom,$dsbtitle:�����̱���
					,$kubun:���� 0��Ԫԭ��,1��������Ҽ۸�
        Output:		���ս��
    \*======================================================================*/
	public function getSalePrice($pricedoc,$dsbtitle,$kubun)
	{
		$pricelist = "";//��ʾ�۸�
		$hdunitprice = "";//���ؼ۸���ϸ��������
		
	    //<li><span class="label">25000</span> <span class="value" data-baseprice="0.2429" data-basecurrency="USD">$0.2429</span></li>
		$onevalue = pq($pricedoc)->find('li');
		foreach($onevalue as $results)
		{
			$pricecnt = pq($results)->find('.label')->html();//����
			if($pricecnt == "")
			{
				$pricecnt = "1";
			}
			$untprice = pq($results)->find('.value')->html();//������Ӧ����
			//echo $untprice;
			$untprice = str_replace("¥","",$untprice);
			$untprice = str_replace("$","",$untprice);
			//echo $untprice;
			$pricelist = round($untprice*8.2,2);
			//echo $pricelist;
			//����Ҽ۸� element14 Asia-Pacific=e����		
			if($untprice != "0")
			{
				//$pricelist .= $this->setRmbPrice($pricecnt,$untprice,$kubun,$dsbtitle);
				break;
				//$pricelist .= "<span class='tx_l'>".$pricecnt ."</span><span>".$untprice ."</span>";
				//$hdunitprice .= str_replace(",","",$pricecnt)."|".$untprice.",";
			}			
		}
		//$pricedata = array($pricelist,$hdunitprice);
		//�������ս��
		return $pricelist;
	}
	
	/*======================================================================*\
        Function:	setRmbPrice
        Purpose:	���ش�������������ۼ۸��б�
                    (����*����*����)
        Input:		$untprice:����۸�,$dsbtitle:�����̱���
					,$kubun:���� 0��Ԫԭ��,1��������Ҽ۸�
        Output:		���ս��
    \*======================================================================*/
	private function setRmbPrice($pricecnt,$untprice,$kubun,$dsbtitle)
	{ 
		$paravalue = round($untprice*8.2,2);
		return $paravalue;
	}
	/*======================================================================*\
        Function:	setEciaPrice
        Purpose:	���ش�������������ۼ۸��б�
                    (����*����*����)
        Input:		$pricedom:����۸�,$dsbtitle:�����̱���
					,$kubun:���� 0��Ԫԭ��,1��������Ҽ۸�
        Output:		���ս��
    \*======================================================================*/
	function setEciaPrice($pricedom,$kubun,$dsbtitle)
	{
		$pricelist = "";
		$hdunitprice = "";
		$monevalue = pq($pricedom)->find('tr');
		foreach($monevalue as $mresults)
		{
			//$pricelist .= pq($mresults)->find('td:eq(0)')->html();
			$mpricecnt = str_replace(" ","",pq($mresults)->find('td:eq(0)')->text());
			$muntprice = pq($mresults)->find('td:eq(1)')->html();
			if($muntprice !="" and $muntprice !="Quote")
			{
				$pricelist .= "<span class='base-price'>".str_replace(",","",$mpricecnt).$this->setRmbPrice($muntprice,$kubun,$dsbtitle)."</span><br>";
				$hdunitprice .= str_replace(",","",$mpricecnt)."|".$this->setRmbPrice($muntprice,$kubun,$dsbtitle).",";
				//$hdunitprice = $this->getMouserRMB($muntprice);
			}
		}
		$pricedata = array($pricelist,$hdunitprice);
		return $pricedata;
	}
	/*======================================================================*\
        Function:	setChipsItemList
        Purpose:	��ȡ��ѯ������Ϣ
        Input:		$distkey:�����̱���
					$items:��ʾ��Ϣ��Ŀ
					$suppname ��Ӧ������
					$dsbtitle ��Ӧ�̱��� 
        Output:		���ս��
    \*======================================================================*/
	public function setChipsItemList($distkey,$items,$loopcnt,$suppname,$dsbtitle)
	{	
		$oneitemdata = "";
		if($loopcnt == "ecia")
		{
			$itemcode = $this->getRandStr($length=5);
		} else if($loopcnt == "netcom"){
			$itemcode = $this->getRandStr($length=5);
		} else {
			$itemcode = $dsbtitle.$loopcnt;
		}
		
		
		if($loopcnt % 2 == 0)
		{
			$oneitemdata .= "<tr class='even' id='".$itemcode."'>";
		} else {
			$oneitemdata .= "<tr id='".$itemcode."' >";
		}
		//Part #
		$oneitemdata .= "<td style='text-align:left;padding-left:5px'>".$items[0]."</td>";
		//������
		$oneitemdata .= "<td style='text-align:left;padding-left:5px' title='".$items[1]."'>".$items[1]."</td>";
		//˵��
		$oneitemdata .= "<td style='text-align:left;padding-left:5px'>".$items[6]."</td>";
		//���
		$oneitemdata .= "<td>".$items[3]."</td><input type='hidden' id='hidprice".$loopcnt."' value='".$items[5]."'>";
		//���ع�Ӧ��
		$oneitemdata .= "";
		//���ڼ۸�
		//$oneitemdata .= "<td><table>".$items[4]."</table></td>";
		//$oneitemdata .= "<td><input type='text' id='txtprice".$loopcnt."' style='width:60px;text-align:right;height:25px;line-height:25px;' onblur=\"BuyCase('".$loopcnt."')\"></td>";
		$oneitemdata .= "<td><input type='text' id='txtprice".$loopcnt."' onkeyup =\"value=value.replace(/[^\d]/g,'')\" onpaste=\"return false;\" style='width:60px;text-align:right;height:25px;line-height:25px;' onblur=\"BuyCase('".$loopcnt."','".$items[3]."')\"></td>";
		
		$oneitemdata .= "<td><span id='spanpri".$loopcnt."'></span><span id='spanprice".$loopcnt."'></span></td>";
		//����/ѯ��
		$oneitemdata .= "<td ><a class='btn-yellow' onclick=\"javascript:GotBuyPart('".$itemcode."','".$loopcnt."','".$suppname."');\">�� ��</a></td>";
		$oneitemdata .= "</tr>";
		//return $itemcode;
		return $oneitemdata;
	}
	/*======================================================================*\
        Function:	setShowDatas
        Purpose:	��ʾһ����Ӧ������
        Input:		��Ӧ��ȫ���ͺ�����
					(�ͺš����ơ��������۸񡢿�桢�����̱���)
        Output:		�����Ľ��
    \*======================================================================*/
	public function  setShowDatas($partlist,$diskey,$showPrice)
	{
		$distrilist = "";
		$distrilist .= "<div><h4>��ʷ������:".$showPrice."</h4></div>";
		$distrilist .= "<table id=".$diskey." class='ic-list-tbl pro-list-tbl' style='margin-bottom:-1100px;'>";
		$distrilist .= "<colgroup>";
		$distrilist .= "<col width='240'/>";
		$distrilist .= "<col width='130'/>";
		$distrilist .= "<col width='401'/>";
		$distrilist .= "<col width='110'/>";
		$distrilist .= "<col width='140'/>";
		$distrilist .= "<col width='170'/>";
		$distrilist .= "<col width='120'/>";
		$distrilist .= "</colgroup>";
		$distrilist .= "<thead>";
		//$distrilist .= "<tr><td colspan='6' class='finditem'>".$diskey."</td></tr>";
		$distrilist .= "<tr>";
		$distrilist .= "<th>������������</th>";
		$distrilist .= "<th>������</th>";
		$distrilist .= "<th>˵��</th>";
		$distrilist .= "<th>���</th>";
		//$distrilist .= "<th>��ʷ������</th>";
		$distrilist .= "<th>��������</th>";
		$distrilist .= "<th>����۸�</th>";
		$distrilist .= "<th>����</th>";
		$distrilist .= "</tr>";
		$distrilist .= "</thead>";
		$distrilist .= "<tbody>";

		$distrilist .= $partlist;
		
		$distrilist .= "</tbody>";
		$distrilist .= "</table>";
		return  $distrilist;
	}
	/*======================================================================*\
        Function:	getEciaListTitle
        Purpose:	��ȡ��ѯ���ͷ����Ϣ
        Input:		$dsbtitle:�����̱���
					$dsbname:����������
        Output:		���ս��
    \*======================================================================*/
	public function getEciaListTitle($dsbtitle,$dsbname,$logoname)
	{
		$chipstitle = "";
		$precode = trim($dsbtitle);
		//echo $dsbname."<br>";
		if($dsbname == "Mouser Electronics")
		{
			$precode = "topmouser";
		} else if($dsbname == "Digi-Key"){
			$precode = "topdigikey";
		}  else if($dsbname == "Arrow Electronics"){
			$dsbname = "Arrow Electronics";
		} else if($dsbname == "Future Electronics"){
			$precode = "topfuture";
		} else if($dsbname == "Avnet"){
			$precode = "topavnet";
		}
		if($dsbtitle !="")
		{
			$chipstitle .= "<div id='chip".$precode."' class='sort-wrap'>";
			$chipstitle .= "<div id='title".$dsbname."'  class='sort-area'>";
			$chipstitle .= "<span class='sort-select'><img src='../../Public/Home/Images/logo/1577_fc.gif'></span>";
			$chipstitle .= "<span class='item-tip'>".$dsbname." </span>";
			$chipstitle .= "</div>";
			$chipstitle .= "</div>";
		}
		$disdata = array($chipstitle,$dsbtitle);
		return $disdata;
	}
	/*======================================================================*\
        Function:	getNetcomListTitle
        Purpose:	��ȡ��ѯ���ͷ����Ϣ
        Input:		$dsbtitle:�����̱���
					$dsbname:����������
        Output:		���ս��
    \*======================================================================*/
	public function getNetcomListTitle($dsbtitle,$dsbname)
	{
		$chipstitle = "";
		$precode = $dsbtitle;
		if($dsbtitle !="")
		{
			$chipstitle .= "<div id='chip".$precode."' class='sort-wrap'>";
			$chipstitle .= "<div id='title".$dsbname."'  class='sort-area'>";
			$chipstitle .= "<span class='sort-select'><img src='../Public/Home/Images/logo/chipsonline.png'></span>";
			$chipstitle .= "<span class='item-tip'>�����ֻ�:��ʵ�����棬��Ҫѯ��</span>";
			$chipstitle .= "</div>";
			$chipstitle .= "</div>";
		}
		$disdata = array($chipstitle,$dsbtitle);
		return $disdata;
	}
	/*======================================================================*\
        Function:	getChipListTitle
        Purpose:	��ȡ��ѯ���ͷ����Ϣ
        Input:		$dsbtitle:�����̱���
        Output:		���ս��
    \*======================================================================*/
	public function getChipListTitle($dsbtitle)
	{
		$chipstitle = "";
		$precode = $dsbtitle;
		
		$logoname = "chipsonline.png";
		$dsbtitle = "findchips";

		if($dsbtitle !="")
		{
			if($precode == "1577_fc")
			{
				$precode = "topmouser";
			} else if($precode == "1588_fc"){
				$precode = "topdigikey";
			}  else if($precode == "1538_fc"){
				$precode = "toparrow";
			} else if($precode == "1555_fc"){
				$precode = "topfuture";
			} else if($precode == "1562_fc"){
				$precode = "topavnet";
			} else if($precode == "2953375_fc"){
				$precode = "topelement";
			}
			//echo $logoname."<br>";
			$chipstitle .= "<div id='chip".$precode."' class='sort-wrap'>";
			$chipstitle .= "<div id='title".$dsbtitle."'  class='sort-area'>";
			//$chipstitle .= "<span class='sort-select'><a href='javascript:void(0)' >".$dsbtitle."</a></span>";
			$chipstitle .= "<span class='sort-selectt'>".$dsbtitle."</span>";
			$chipstitle .= "<span class='item-tip'>".$dsbtitle." </span>";
			$chipstitle .= "</div>";
			$chipstitle .= "</div>";
		}
		$disdata = array($chipstitle,$dsbtitle);
		return $disdata;
	}
	/*======================================================================*\
        Function:	setEleTitle
        Purpose:	�趨element14��ͷ����Ϣ
        Input:		
        Output:		���ս��
    \*======================================================================*/
	function setEleTitle()
	{
		$chipstitle = "";
		$precode = "topelement";
		$dsbtitle = "element14";
	    $logoname = "element14.png";
		if($dsbtitle !="")
		{
			//echo $logoname."<br>";
			$chipstitle .= "<div id='chip".$precode."' class='sort-wrap'>";
			$chipstitle .= "<div id='title".$dsbtitle."'  class='sort-area'>";
			//$chipstitle .= "<span class='sort-select'><a href='javascript:void(0)' >".$dsbtitle."</a></span>";
			$chipstitle .= "<span class='sort-selectt'><img src='../Public/Home/Images/logo/".$logoname."'></span>";
			$chipstitle .= "<span class='item-tip'>".$dsbtitle." </span>";
			$chipstitle .= "</div>";
			$chipstitle .= "</div>";
		}
		$disdata = array($chipstitle,$dsbtitle);
		return $disdata;
	}
	/*======================================================================*\
        Function:	getRandStr
        Purpose:	��������ַ���
        Input:		$length:�ַ�������
        Output:		���ս��
    \*======================================================================*/
	function getRandStr($length) {  
		$str = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'; 
		$randString = ''; 
		$len = strlen($str)-1; 
		for($i = 0;$i < $length;$i ++){ 
			$num = mt_rand(0, $len); 
			$randString .= $str[$num]; 
		} 
		return $randString ;  
	}
	/*======================================================================*\
        Function:	getEciaShowName
        Purpose:	����ECIA��ʾ��Ӧ������
        Input:		$dsbname:��Ӧ������
        Output:		���ս��
    \*======================================================================*/
	function getEciaShowName($dsbname)
	{
		$showname = "";
		$logoname = "chipsonline.png";
		if($dsbname == "AESCO Electronics")
		{
			$logoname = "AESCO.png";
		} else if($dsbname == "Kreger Components") {
			$logoname = "Kreger.png";
		} else if($dsbname == "Dove Electronic") {
			$logoname = "Dove.png";
		} else if($dsbname == "Marsh Electronics") {
			$logoname = "Marsh.png";
		} else if($dsbname == "East Coast Microwave") {
			$logoname = "East_Coast_Microwave_Logo.png";
		} else if($dsbname == "ECCO") {
			$logoname = "ecco.png";
		} else if($dsbname == "Microwave Components, Inc.") {
			$logoname = "microwavecomponents.png";
		} else if($dsbname == "Astrex") {
			$logoname = "Astrex.png";
		} else if($dsbname == "Electro Enterprises") {
			$logoname = "ElectroEnterprises.png";
		} else if($dsbname == "Schuster Electronics") {
			$logoname = "Schuster.png";
		} else if($dsbname == "Avnet USI") {
			$logoname = "AvnetUSI.png";
		} else if($dsbname == "Electro Sonic") {
			$logoname = "ElectroSonic.png";
		} else if($dsbname == "New Yorker Electronics") {
			$logoname = "NewYorkerElectronics.png";
		} else if($dsbname == "Score Electronics") {
			$logoname = "ScoreElectronics.png";
		} else if($dsbname == "Brothers Electronics") {
			$logoname = "Brothers.png";
		} else if($dsbname == "SMD, Inc.") {
			$logoname = "SMD.png";
		} else if($dsbname == "Carlton-Bates Company") {
			$logoname = "CarltonBates.png";
		} else if($dsbname == "Omni Pro Electronics") {
			$logoname = "OmniPro.png";
		} else if($dsbname == "Straight Road Electronics") {
			$logoname = "StraightRoad.png";
		} else if($dsbname == "CDM Electronics") {
			$logoname = "CDM.png";
		} else if($dsbname == "Symmetry Electronics") {
			$logoname = "Symmetry.png";
		} else if($dsbname == "Peerless Electronic Supplies") {
			$logoname = "Peerless.png";
		} else if($dsbname == "Tonar Industries") {
			$logoname = "Tonar.png";
		} else if($dsbname == "Components Center") {
			$logoname = "ComponentsCenter.png";
		} else if($dsbname == "Hammond Electronics") {
			$logoname = "Hammond.png";
		} else if($dsbname == "PEI-Genesis") {
			$logoname = "PEIGenesis.png";
		} else if($dsbname == "Hawk Electronics") {
			$logoname = "Hawk.png";
		} else if($dsbname == "Pridmore Corporation") {
			$logoname = "Pridmore.png";
		} else if($dsbname == "WPG Americas") {
			$logoname = "WPG.png";
		} else if($dsbname == "Dependable Component Supply") {
			$logoname = "DependableComponentSupply.png";
		} else if($dsbname == "Avnet") {//***
			$logoname = "Avnet.png";
		} else if($dsbname == "Newark element14") {
			$logoname = "Newark.png";
		}  else if($dsbname == "Mouser Electronics") {//***
			$logoname = "Mouser.png";
		}  else if($dsbname == "Arrow Electronics") {//***
			$logoname = "Arrow.png";
		}  else if($dsbname == "Digi-Key") {//***
			$logoname = "DigiKey.png";
		}  else if($dsbname == "Future Electronics") {//***
			$logoname = "Future.png";
		} else if($dsbname == "Onlinecomponents.com") {//***
			$logoname = "Onlinecom.png";
		}
		else {
			$showname = "hid";
		}
	   return $eciatitledata = array($showname,$logoname);;
	}
	/*======================================================================*\
        Function:	getPartDetail
        Purpose:	��ȡ�ͺž������
        Input:		$detail:������ϸ
        Output:		���ս��
    \*======================================================================*/
	function getPartDetail($detail)
	{
		$onevalue = pq($detail)->find('tr');
		$showinfo = "";
		$onetd = "";
		$loop = 0;
		foreach($onevalue as $results)
		{
			$loop = $loop + 1;
			$infoname =  pq($results)->find('th')->html();
			$infocode =  pq($results)->find('td')->html();
			if($infocode == "")
			{
				break;
			}			
			$onetd .= "<td>".$infoname.":".$infocode."</td>";
			if($loop % 4 == 0)
			{
				$showinfo .= "<tr>".$onetd."</tr>";
				$onetd = "";
			}
			
		}
		if($onetd !="")
		{
			$showinfo .= "<tr>".$onetd."</tr>";
		}
		return "<table>".$showinfo."</table>";
	}
	/*======================================================================*\
        Function:	getElementStock
        Purpose:	��ȡ�ͺſ��
        Input:		$stockTable:�����Ϣ
        Output:		���ս��
    \*======================================================================*/
	function getElementStock($stockTable)
	{
		$onevalue = pq($stockTable)->find('tr');
		$stock = 0;
		foreach($onevalue as $results)
		{
			$stock = $stock + pq($results)->find('td:eq(1)')->html();
		}
		
		return $stock;
	}
	function getOneElementStock($stock)
	{
		$onevalue = pq($stock)->find('b');
		$stock = 0;
		foreach($onevalue as $results)
		{
			$stock = $stock + pq($results)->html();
		}
		
		return $stock;
	}
	/*======================================================================*\
        Function:	getElePrice
        Purpose:	��ȡ�ͺſ��
        Input:		$priceTable:�۸���Ϣ
        Output:		���ս��
    \*======================================================================*/
	function getElePrice($priceTable,$kubun)
	{
		$pricelist = "";
		$hdunitprice = "";
		$onevalue = pq($priceTable)->find('tr');
		foreach($onevalue as $results)
		{
			$pricecnt = pq($results)->find(".PriceBreakFromContent")->html();//����
			$untprice = pq($results)->find("span")->html();//������Ӧ����
			if($kubun == "0" && $pricecnt !="")
			{
				$untprice = str_replace("HK$","",$untprice);
				$total = $untprice /6.8;//HK$
				$untprice = sprintf('%.4f', (float)$total);
				$pricelist .= "<span class='base-price'>".trim(str_replace("+","",$pricecnt)).':<dfn>$</dfn>'.$untprice."</span><br>";
				$hdunitprice .= str_replace("+","",$pricecnt)."|".$untprice.",";
			} else if($pricecnt !=""){
				$untprice = str_replace("HK$","",$untprice);
				$pricelist .= "<span class='base-price'>".trim(str_replace("+","",$pricecnt)).':<dfn>��</dfn>'.$untprice."</span><br>";
				$hdunitprice .= str_replace("+","",$pricecnt)."|".$untprice.",";
			}			
		}
		$pricedata = array($pricelist,$hdunitprice);
		//�������ս��
		return $pricedata;
	}
	function getOneElePrice($priceTable,$kubun)
	{
		$pricelist = "";
		$hdunitprice = "";
		$onevalue = pq($priceTable)->find('tr');
		foreach($onevalue as $results)
		{
			$pricecnt = trim(str_replace(" ","",pq($results)->find("td:eq(0)")->html()));//����
			$pricecnt = trim(str_replace("+","-",$pricecnt));
			$pricecntArray = explode("-", $pricecnt);
			$untprice = pq($results)->find("td:eq(1)")->html();//������Ӧ����
			//echo $untprice."=========<br>";
			if($kubun == "0" && trim($untprice) !="")
			{
				$untprice = str_replace("HK$","",$untprice);
				$total = $untprice /6.8;//HK$
				$untprice = sprintf('%.4f', (float)$total);
				$pricelist .= "<span class='base-price'>".trim(str_replace("+","",$pricecntArray[0])).':<dfn>$</dfn>'.trim($untprice)."</span><br>";
				$hdunitprice .= str_replace("+","",$pricecnt)."|".$untprice.",";
			} else if($untprice !=""){
				$untprice = str_replace("HK$","",$untprice);
				$pricelist .= "<span class='base-price'>".trim(str_replace("+","",$pricecntArray[0])).':<dfn>��</dfn>'.trim($untprice)."</span><br>";
				$hdunitprice .= str_replace("+","",$pricecnt)."|".$untprice.",";
			}			
		}
		$pricedata = array($pricelist,$hdunitprice);
		//�������ս��
		return $pricedata;
	}
}
?>