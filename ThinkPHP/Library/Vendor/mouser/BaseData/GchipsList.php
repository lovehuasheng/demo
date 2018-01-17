<?php
/*************************************************
 *
 * GchipsList - the PHP net client
 * Author: rong
 * Copyright (c): 2014-2014, all rights reserved
 * Version: 1.0.0
 * 对抓取的数据进行二次拆分处理
 *
 *************************************************/
class GchipsList{
	
	var $datahtml = "";//型号数据结果
	var $onedisthtml = "";//一个代理商的数据结果
	
	function __construct()
	{
		$this->onedisthtml = "";
		$this->datahtml = "";
	}
	/*======================================================================*\
        Function:	getDistributor
        Purpose:	代理商
        Input:		代理商Dom
        Output:		处理后的结果
    \*======================================================================*/
	public function getDistributor($distvalue)
	{
		preg_match('/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png))\"?.+>/i',$distvalue,$match);
		return str_replace('https://media.findchips.com/srp/logos/results/','',$match[1]);
	}
	public function getPDFurl($PDFurl)
	{
		$preg='/<a.*?href="(.*?)".*?>/is';
		
		preg_match_all($preg,$PDFurl,$match);//在$str中搜索匹配所有符合$preg加入$match中
	
		echo $match[1][1];
	}
	/*======================================================================*\
        Function:	getSalePrice
        Purpose:	返回销售价格列表
        Input:		$pricedoc:价格 dom,$dsbtitle:代理商编码
					,$kubun:区分 0美元原价,1销售人民币价格
        Output:		最终结果
    \*======================================================================*/
	public function getMallSalePrice($pricedoc,$dsbtitle,$kubun,$mallPara)
	{
		$pricelist = "";//显示价格
		$hdunitprice = "";//隐藏价格，详细画面利用
		
	    //<li><span class="label">25000</span> <span class="value" data-baseprice="0.2429" data-basecurrency="USD">$0.2429</span></li>
		$onevalue = pq($pricedoc)->find('li');
		foreach($onevalue as $results)
		{
			$pricecnt = pq($results)->find('.label')->html();//数量
			if($pricecnt == "")
			{
				$pricecnt = "1";
			}
			$untprice = pq($results)->find('.value')->html();//数量对应单价
			//echo $untprice;
			$untprice = str_replace("楼","",$untprice);
			$untprice = str_replace("$","",$untprice);
			//echo $untprice;
			$pricelist = round($untprice*$mallPara,2);
			//echo $pricelist;
			//人民币价格 element14 Asia-Pacific=e络盟		
			if($untprice != "0")
			{
				//$pricelist .= $this->setRmbPrice($pricecnt,$untprice,$kubun,$dsbtitle);
				break;
				//$pricelist .= "<span class='tx_l'>".$pricecnt ."</span><span>".$untprice ."</span>";
				//$hdunitprice .= str_replace(",","",$pricecnt)."|".$untprice.",";
			}			
		}
		//$pricedata = array($pricelist,$hdunitprice);
		//返回最终结果
		return $pricelist;
	}
    /*======================================================================*\
        Function:	getSalePrice
        Purpose:	返回销售价格列表
        Input:		$pricedoc:价格 dom,$dsbtitle:代理商编码
					,$kubun:区分 0美元原价,1销售人民币价格
        Output:		最终结果
    \*======================================================================*/
	public function getSalePrice($pricedoc,$dsbtitle,$kubun)
	{
		$pricelist = "";//显示价格
		$hdunitprice = "";//隐藏价格，详细画面利用
		
	    //<li><span class="label">25000</span> <span class="value" data-baseprice="0.2429" data-basecurrency="USD">$0.2429</span></li>
		$onevalue = pq($pricedoc)->find('li');
		foreach($onevalue as $results)
		{
			$pricecnt = pq($results)->find('.label')->html();//数量
			if($pricecnt == "")
			{
				$pricecnt = "1";
			}
			$untprice = pq($results)->find('.value')->html();//数量对应单价
			//echo $untprice;
			$untprice = str_replace("楼","",$untprice);
			$untprice = str_replace("$","",$untprice);
			//echo $untprice;
			$pricelist = round($untprice*8.2,2);
			//echo $pricelist;
			//人民币价格 element14 Asia-Pacific=e络盟		
			if($untprice != "0")
			{
				//$pricelist .= $this->setRmbPrice($pricecnt,$untprice,$kubun,$dsbtitle);
				break;
				//$pricelist .= "<span class='tx_l'>".$pricecnt ."</span><span>".$untprice ."</span>";
				//$hdunitprice .= str_replace(",","",$pricecnt)."|".$untprice.",";
			}			
		}
		//$pricedata = array($pricelist,$hdunitprice);
		//返回最终结果
		return $pricelist;
	}
	
	/*======================================================================*\
        Function:	setRmbPrice
        Purpose:	返回代理商人民币销售价格列表
                    (美金*汇率*参数)
        Input:		$untprice:美金价格,$dsbtitle:代理商编码
					,$kubun:区分 0美元原价,1销售人民币价格
        Output:		最终结果
    \*======================================================================*/
	private function setRmbPrice($pricecnt,$untprice,$kubun,$dsbtitle)
	{ 
		$paravalue = round($untprice*8.2,2);
		return $paravalue;
	}
	/*======================================================================*\
        Function:	setEciaPrice
        Purpose:	返回代理商人民币销售价格列表
                    (美金*汇率*参数)
        Input:		$pricedom:美金价格,$dsbtitle:代理商编码
					,$kubun:区分 0美元原价,1销售人民币价格
        Output:		最终结果
    \*======================================================================*/
	function setEciaPrice($pricedom,$kubun,$dsbtitle)
	{
		$pricelist = "";
		$hdunitprice = "";
		$monevalue = pq($pricedom)->find('tr');
		foreach($monevalue as $mresults)
		{
			//$pricelist .= pq($mresults)->find('td:eq(0)')->html();
			$mpricecnt = str_replace("聽","",pq($mresults)->find('td:eq(0)')->text());
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
        Purpose:	获取查询数据信息
        Input:		$distkey:代理商编码
					$items:显示信息项目
					$suppname 供应商名称
					$dsbtitle 供应商编码 
        Output:		最终结果
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
		//制造商
		$oneitemdata .= "<td style='text-align:left;padding-left:5px' title='".$items[1]."'>".$items[1]."</td>";
		//说明
		$oneitemdata .= "<td style='text-align:left;padding-left:5px'>".$items[6]."</td>";
		//库存
		$oneitemdata .= "<td>".$items[3]."</td><input type='hidden' id='hidprice".$loopcnt."' value='".$items[5]."'>";
		//隐藏供应商
		$oneitemdata .= "";
		//国内价格
		//$oneitemdata .= "<td><table>".$items[4]."</table></td>";
		//$oneitemdata .= "<td><input type='text' id='txtprice".$loopcnt."' style='width:60px;text-align:right;height:25px;line-height:25px;' onblur=\"BuyCase('".$loopcnt."')\"></td>";
		$oneitemdata .= "<td><input type='text' id='txtprice".$loopcnt."' onkeyup =\"value=value.replace(/[^\d]/g,'')\" onpaste=\"return false;\" style='width:60px;text-align:right;height:25px;line-height:25px;' onblur=\"BuyCase('".$loopcnt."','".$items[3]."')\"></td>";
		
		$oneitemdata .= "<td><span id='spanpri".$loopcnt."'></span><span id='spanprice".$loopcnt."'></span></td>";
		//购买/询价
		$oneitemdata .= "<td ><a class='btn-yellow' onclick=\"javascript:GotBuyPart('".$itemcode."','".$loopcnt."','".$suppname."');\">购 买</a></td>";
		$oneitemdata .= "</tr>";
		//return $itemcode;
		return $oneitemdata;
	}
	/*======================================================================*\
        Function:	setShowDatas
        Purpose:	显示一个供应商数据
        Input:		供应商全部型号数据
					(型号、厂牌、描述、价格、库存、代理商编码)
        Output:		处理后的结果
    \*======================================================================*/
	public function  setShowDatas($partlist,$diskey,$showPrice)
	{
		$distrilist = "";
		$distrilist .= "<div><h4>历史交货价:".$showPrice."</h4></div>";
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
		$distrilist .= "<th>制造商零件编号</th>";
		$distrilist .= "<th>制造商</th>";
		$distrilist .= "<th>说明</th>";
		$distrilist .= "<th>库存</th>";
		//$distrilist .= "<th>历史交货价</th>";
		$distrilist .= "<th>购买数量</th>";
		$distrilist .= "<th>购买价格</th>";
		$distrilist .= "<th>操作</th>";
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
        Purpose:	获取查询结果头部信息
        Input:		$dsbtitle:代理商编码
					$dsbname:代理商名称
        Output:		最终结果
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
        Purpose:	获取查询结果头部信息
        Input:		$dsbtitle:代理商编码
					$dsbname:代理商名称
        Output:		最终结果
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
			$chipstitle .= "<span class='item-tip'>国外现货:真实国外库存，需要询价</span>";
			$chipstitle .= "</div>";
			$chipstitle .= "</div>";
		}
		$disdata = array($chipstitle,$dsbtitle);
		return $disdata;
	}
	/*======================================================================*\
        Function:	getChipListTitle
        Purpose:	获取查询结果头部信息
        Input:		$dsbtitle:代理商编码
        Output:		最终结果
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
        Purpose:	设定element14果头部信息
        Input:		
        Output:		最终结果
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
        Purpose:	生成随机字符串
        Input:		$length:字符串长度
        Output:		最终结果
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
        Purpose:	限制ECIA显示供应商数据
        Input:		$dsbname:供应商名字
        Output:		最终结果
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
        Purpose:	获取型号具体参数
        Input:		$detail:参数明细
        Output:		最终结果
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
        Purpose:	获取型号库存
        Input:		$stockTable:库存信息
        Output:		最终结果
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
        Purpose:	获取型号库存
        Input:		$priceTable:价格信息
        Output:		最终结果
    \*======================================================================*/
	function getElePrice($priceTable,$kubun)
	{
		$pricelist = "";
		$hdunitprice = "";
		$onevalue = pq($priceTable)->find('tr');
		foreach($onevalue as $results)
		{
			$pricecnt = pq($results)->find(".PriceBreakFromContent")->html();//数量
			$untprice = pq($results)->find("span")->html();//数量对应单价
			if($kubun == "0" && $pricecnt !="")
			{
				$untprice = str_replace("HK$","",$untprice);
				$total = $untprice /6.8;//HK$
				$untprice = sprintf('%.4f', (float)$total);
				$pricelist .= "<span class='base-price'>".trim(str_replace("+","",$pricecnt)).':<dfn>$</dfn>'.$untprice."</span><br>";
				$hdunitprice .= str_replace("+","",$pricecnt)."|".$untprice.",";
			} else if($pricecnt !=""){
				$untprice = str_replace("HK$","",$untprice);
				$pricelist .= "<span class='base-price'>".trim(str_replace("+","",$pricecnt)).':<dfn>￥</dfn>'.$untprice."</span><br>";
				$hdunitprice .= str_replace("+","",$pricecnt)."|".$untprice.",";
			}			
		}
		$pricedata = array($pricelist,$hdunitprice);
		//返回最终结果
		return $pricedata;
	}
	function getOneElePrice($priceTable,$kubun)
	{
		$pricelist = "";
		$hdunitprice = "";
		$onevalue = pq($priceTable)->find('tr');
		foreach($onevalue as $results)
		{
			$pricecnt = trim(str_replace("聽","",pq($results)->find("td:eq(0)")->html()));//数量
			$pricecnt = trim(str_replace("+","-",$pricecnt));
			$pricecntArray = explode("-", $pricecnt);
			$untprice = pq($results)->find("td:eq(1)")->html();//数量对应单价
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
				$pricelist .= "<span class='base-price'>".trim(str_replace("+","",$pricecntArray[0])).':<dfn>￥</dfn>'.trim($untprice)."</span><br>";
				$hdunitprice .= str_replace("+","",$pricecnt)."|".$untprice.",";
			}			
		}
		$pricedata = array($pricelist,$hdunitprice);
		//返回最终结果
		return $pricedata;
	}
}
?>