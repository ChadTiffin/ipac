<?php
//HTML2PDF by Clément Lavoillotte
//ac.lavoillotte@noos.fr
//webmaster@streetpc.tk
//http://www.streetpc.tk

require('Fpdf.php');

//function hex2dec
//returns an associative array (keys: R,G,B) from
//a hex html code (e.g. #3FE5AA)
function hex2dec($couleur = "#000000"){
	$R = substr($couleur, 1, 2);
	$rouge = hexdec($R);
	$V = substr($couleur, 3, 2);
	$vert = hexdec($V);
	$B = substr($couleur, 5, 2);
	$bleu = hexdec($B);
	$tbl_couleur = array();
	$tbl_couleur['R']=$rouge;
	$tbl_couleur['V']=$vert;
	$tbl_couleur['B']=$bleu;
	return $tbl_couleur;
}

//conversion pixel -> millimeter at 72 dpi
function px2mm($px){
	return $px*25.4/72;
}

function txtentities($html){
	$trans = get_html_translation_table(HTML_ENTITIES);
	$trans = array_flip($trans);
	return strtr($html, $trans);
}
////////////////////////////////////

class Pdfhtml extends FPDF
{
//variables of html parser
protected $B;
protected $I;
protected $U;
protected $HREF;
protected $fontList;
protected $issetfont;
protected $issetcolor;
protected $lineheight = 0.25;
protected $list_point = "bullet";
protected $bullet = true;
protected $list_count = 1;
protected $margin_indent = 0.4;
protected $absolute_left_margin = 0.8;
protected $left_margin = 0.8;
protected $top_margin = 0.3;
protected $api_base = "";
protected $last_element = "";
protected $image_width = 250/72;

function __construct($orientation='P', $unit='mm', $format='A4',$api_base="")
{
	//Call parent constructor
	parent::__construct($orientation,$unit,$format);
	//Initialization
	$this->B=0;
	$this->I=0;
	$this->U=0;
	$this->HREF='';
	$this->fontlist=array('arial', 'times', 'courier', 'helvetica', 'symbol');
	$this->issetfont=false;
	$this->issetcolor=false;
	$this->api_base = $api_base;
}

function addReportPage() {
	$this->AddPage();

	//create header
	$this->Image("assets/logo.jpg",$this->absolute_left_margin,$this->top_margin,0,1,'');

	$this->SetLineWidth(0.025);
	$this->setDrawColor(60,85,75);
	$this->Line($this->absolute_left_margin,$this->top_margin+1.05,8.5-$this->absolute_left_margin,$this->top_margin+1.05);
	// end header
    $this->setY(1.4);
	//create footer
	$this->Image("assets/report_footer.png",0,9.3,8.5,0,'PNG');
}

function WriteHTML($html,$lineheight=0.25)
{
	//HTML parser
	$html=strip_tags($html,"<b><u><i><a><img><p><br><strong><em><font><tr><blockquote><br /><ul><ol><li><hr>"); //supprime tous les tags sauf ceux reconnus

	$html=str_replace("\n",' ',$html); //remplace retour à la ligne par un espace
	$html=str_replace("&nbsp;", " ", $html);
	$html=str_replace("<br>", "\n", $html);
	$html=str_replace("<br />", "\n", $html);

	$a=preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE); //éclate la chaîne avec les balises
	foreach($a as $i=>$e)
	{

		if($i%2==0)
		{
			//Text
			if($this->HREF)
				$this->PutLink($this->HREF,$e,$lineheight);
			else 
				$this->Write($lineheight,stripslashes(txtentities($e)));
		}
		else
		{
			//Tag
			if($e[0]=='/')
				$this->CloseTag(strtoupper(substr($e,1)),$lineheight);
			else
			{
				//Extract attributes
				$a2=explode(' ',$e);
				$tag=strtoupper(array_shift($a2));
				$attr=array();
				foreach($a2 as $v)
				{
					if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3))
						$attr[strtoupper($a3[1])]=$a3[2];
				}
				$this->OpenTag($tag,$attr,$lineheight);
			}
		}
	}
}

function OpenTag($tag, $attr,$lineheight=0.25)
{

    $setLastElement = true;
	//Opening tag
	switch($tag){
		case 'STRONG':
			$this->SetStyle('B',true);
			break;
		case 'EM':
			$this->SetStyle('I',true);
			break;
		case 'B':
		case 'I':
		case 'U':
			$this->SetStyle($tag,true);
			break;
		case 'A':
			$this->HREF=$attr['HREF'];
			break;
		case 'IMG':
			if (isset($attr['SRC'])) {
				if (strpos($attr['SRC'], "http") == -1) {
					//append the path to the source
					$attr['SRC'] = $this->api_base."image/image/".$attr['SRC'];
				}

                $width = $this->image_width;

                //check if line wrap needs to occur
                if ($this->getPageWidth() - ($this->GetX() + $width) <= 0) {
                    $this->SetY($this->getY() + $width*0.75);
                }

                if ($this->getPageHeight() - ($this->GetY() + ($width*0.75)) - 2 <= 0) {
                    $this->addReportPage();
                    //$this->last_element = null;
                    $setLastElement = false;
                }

				

//                if (file_exists($attr['SRC'])) {

				    $this->Image($attr['SRC'], $this->GetX(), $this->GetY(), $width, 0);
            
    				$this->SetX($this->GetX()+$width);

    				
    				
                //}
			}
			break;
		case 'TR':
		case 'BLOCKQUOTE':
            if ($this->last_element == "IMG") 
                $this->SetY($this->getY() + ($this->image_width)*0.75);
            else 
                $this->Ln($lineheight);

		case 'BR':
			if ($this->last_element == "IMG") 
                $this->SetY($this->getY() + ($this->image_width)*0.75);
            else 
                $this->Ln($lineheight);

			break;
		case 'P':
            if ($this->last_element == "IMG") 
                $this->SetY($this->getY() + ($this->image_width)*0.75);
            else 
                $this->Ln($lineheight);

			break;
		case 'UL':
            if ($this->last_element == "IMG") 
                $this->SetY($this->getY() + ($this->image_width)*0.75);

			$this->left_margin += $this->margin_indent;
			$this->list_point = "bullet";
			$this->setLeftMargin($this->left_margin);
			break;
		case 'OL':
            if ($this->last_element == "IMG") 
                $this->SetY($this->getY() + ($this->image_width)*0.75);

			$this->left_margin += $this->margin_indent;
			$this->list_point = "number";
			$this->list_count = 1;
			$this->setLeftMargin($this->left_margin);
			break;
		case 'LI':
			$this->bullet = true;

			if ($this->bullet) {
				if ($this->list_point == 'bullet') {
					$y = $this->GetY();
					$this->Text($this->left_margin-0.1,$y+($lineheight*1.75),"•");
				}
				elseif ($this->list_point == 'number') {
					$y = $this->GetY();
					$this->Text($this->left_margin-0.1,$y+($lineheight*1.75),$this->list_count.".");
					
					$this->list_count++;
				}
				$this->bullet = false;
			}

			$this->Ln($lineheight);
			break;
        case "HR":
            if ($this->last_element == "IMG") 
                $this->SetY($this->getY() + ($this->image_width)*0.75);
            else 
                $this->Ln($lineheight);

            $this->Line($this->left_margin,$this->getY(),$this->getPageWidth() - $this->left_margin,$this->getY());

            break;
		case 'FONT':
			if (isset($attr['COLOR']) && $attr['COLOR']!='') {
				$coul=hex2dec($attr['COLOR']);
				$this->SetTextColor($coul['R'],$coul['V'],$coul['B']);
				$this->issetcolor=true;
			}
			if (isset($attr['FACE']) && in_array(strtolower($attr['FACE']), $this->fontlist)) {
				$this->SetFont(strtolower($attr['FACE']));
				$this->issetfont=true;
			}
			break;
	}

    if ($setLastElement)
        $this->last_element = $tag;
}

function CloseTag($tag,$lineheight = 0.25)
{
	//Closing tag
	if($tag=='STRONG')
		$tag='B';
	if($tag=='EM')
		$tag='I';
	if($tag=='B' || $tag=='I' || $tag=='U')
		$this->SetStyle($tag,false);
	if($tag=='A')
		$this->HREF='';
	if ($tag == 'UL' || $tag == 'OL') {
		//var_dump("Close list");
		$this->left_margin -= $this->margin_indent;
		$this->setLeftMargin($this->left_margin);
		$this->Ln($lineheight);
	}
	if ($tag == 'P')
		$this->Ln($lineheight);
	if($tag=='FONT'){
		if ($this->issetcolor==true) {
			$this->SetTextColor(0);
		}
		if ($this->issetfont) {
			$this->SetFont('arial');
			$this->issetfont=false;
		}
	}
}

function SetStyle($tag, $enable)
{
	//Modify style and select corresponding font
	$this->$tag+=($enable ? 1 : -1);
	$style='';
	foreach(array('B','I','U') as $s)
	{
		if($this->$s>0)
			$style.=$s;
	}
	$this->SetFont('',$style);
}

function PutLink($URL, $txt,$lineheight=0.25)
{
	//Put a hyperlink
	$this->SetTextColor(0,0,255);
	$this->SetStyle('U',true);
	$this->Write($lineheight,$txt,$URL);
	$this->SetStyle('U',false);
	$this->SetTextColor(0);
}

/**
* Create the Small Caps effect in a Cell
* @author constantin teleman
*/
function CellSmallCaps($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
{
    //Output a cell
    $k=$this->k;
    if($this->y+$h>$this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak())
    {
        //Automatic page break
        $x=$this->x;
        $ws=$this->ws;
        if($ws>0)
        {
            $this->ws=0;
            $this->_out('0 Tw');
        }
        $this->addReportPage();
        $this->x=$x;
        if($ws>0)
        {
            $this->ws=$ws;
            $this->_out(sprintf('%.3F Tw',$ws*$k));
        }
    }
    if($w==0)
        $w=$this->w-$this->rMargin-$this->x;
    $s='';
    if($fill || $border==1)
    {
        if($fill)
            $op=($border==1) ? 'B' : 'f';
        else
            $op='S';
        $s=sprintf('%.2F %.2F %.2F %.2F re %s ',$this->x*$k,($this->h-$this->y)*$k,$w*$k,-$h*$k,$op);
    }
    if(is_string($border))
    {
        $x=$this->x;
        $y=$this->y;
        if(strpos($border,'L')!==false)
            $s.=sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-$y)*$k,$x*$k,($this->h-($y+$h))*$k);
        if(strpos($border,'T')!==false)
            $s.=sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-$y)*$k);
        if(strpos($border,'R')!==false)
            $s.=sprintf('%.2F %.2F m %.2F %.2F l S ',($x+$w)*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
        if(strpos($border,'B')!==false)
            $s.=sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-($y+$h))*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
    }
    if($txt!=='')
    {
        if($align=='R')
            $dx=$w-$this->cMargin-$this->GetStringWidth($txt);
        elseif($align=='C')
            $dx=($w-$this->GetStringWidth($txt))/2;
        else
            $dx=$this->cMargin;
        if($this->ColorFlag)
            $s.='q '.$this->TextColor.' ';
        $txt2=str_replace(')','\\)',str_replace('(','\\(',str_replace('\\','\\\\',$txt)));

        $firstDx = $dx;
        for ($i=0; $i<strlen($txt2); $i++) {
            $letter = $txt2[$i];
            $upper = strpos("ABCDEFGHIJKLMNOPQRSTUVWXZ0123456789`!@#$%^&*()_=+|'\";:{}[]?,.<>",$letter);
            if ($upper === false) { // the letter is lowercase
                if($i==0){ // for the first char
                    $s.=sprintf(' BT /F%d %.2F Tf',$this->CurrentFont['i'],$this->FontSizePt*0.8);
                    $s.=sprintf(' %.2F %.2F Td (%s) Tj',($this->x+$dx)*$k,($this->h-($this->y+.5*$h+.3*$this->FontSize))*$k,strtoupper($letter));
                    $s.=sprintf(' /F%d %.2F Tf ET',$this->CurrentFont['i'],$this->FontSizePt);
                } else {  // if there is no first char
                    $s.=sprintf(' BT /F%d %.2F Tf',$this->CurrentFont['i'],$this->FontSizePt*0.8);
                    $dx+=($this->GetStringWidth(substr($txt2,$i-1,1)))*1.02;
                    $s.=sprintf(' %.2F %.2F Td (%s) Tj',($this->x+$dx)*$k,($this->h-($this->y+.5*$h+.3*$this->FontSize))*$k,strtoupper($letter));
                    $s.=sprintf(' /F%d %.2F Tf ET',$this->CurrentFont['i'],$this->FontSizePt);
                }
            } else { //case the letter is uppercase
                if($i == 0){ //for the first char
                    $s.=sprintf(' BT %.2F %.2F Td (%s) Tj ET',($this->x+$dx)*$k,($this->h-($this->y+.5*$h+.3*$this->FontSize))*$k,$letter);
                } else {  // if there is no first char
                    $dx+=($this->GetStringWidth(substr($txt2,$i-1,1)))*1.02;
                    $s.=sprintf(' BT %.2F %.2F Td (%s) Tj ET',($this->x+$dx)*$k,($this->h-($this->y+.5*$h+.3*$this->FontSize))*$k,$letter);
                }
            }
        }
        
        if($this->underline)
            $s.=' '.$this->_dounderline($this->x+$firstDx,$this->y+.5*$h+.3*$this->FontSize,$txt);
        if($this->ColorFlag)
            $s.=' Q';
        if($link)
            $this->Link($this->x+$firstDx,$this->y+.5*$h-.5*$this->FontSize,$this->GetStringWidth($txt),$this->FontSize,$link);
    }
    if($s)
        $this->_out($s);
    $this->lasth=$h;
    if($ln>0)
    {
        //Go to next line
        $this->y+=$h;
        if($ln==1)
            $this->x=$this->lMargin;
    }
    else
        $this->x+=$w;
}

/**
* Create the Small Caps effect in a MultiCell
* @author constantin teleman
*/
function MultiCellSmallCaps($w, $h, $txt, $border=0, $align='', $fill=false)
{
    //Output text with automatic or explicit line breaks
    $cw=&$this->CurrentFont['cw'];
    if($w==0)
        $w=$this->w-$this->rMargin-$this->x;
    $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
    $s=str_replace("\r",'',$txt);
    $nb=strlen($s);
    if($nb>0 && $s[$nb-1]=="\n")
        $nb--;
    $b=0;
    if($border)
    {
        if($border==1)
        {
            $border='LTRB';
            $b='LRT';
            $b2='LR';
        }
        else
        {
            $b2='';
            if(strpos($border,'L')!==false)
                $b2.='L';
            if(strpos($border,'R')!==false)
                $b2.='R';
            $b=(strpos($border,'T')!==false) ? $b2.'T' : $b2;
        }
    }
    $sep=-1;
    $i=0;
    $j=0;
    $l=0;
    $ns=0;
    $nl=1;
    while($i<$nb)
    {
        //Get next character
        $c=$s[$i];
        if($c=="\n")
        {
            //Explicit line break
            if($this->ws>0)
            {
                $this->ws=0;
                $this->_out('0 Tw');
            }
            $this->CellSmallCaps($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
            $i++;
            $sep=-1;
            $j=$i;
            $l=0;
            $ns=0;
            $nl++;
            if($border && $nl==2)
                $b=$b2;
            continue;
        }
        if($c==' ')
        {
            $sep=$i;
            $ls=$l;
            $ns++;
        }
        $l+=$cw[$c];
        if($l>$wmax)
        {
            //Automatic line break
            if($sep==-1)
            {
                if($i==$j)
                    $i++;
                if($this->ws>0)
                {
                    $this->ws=0;
                    $this->_out('0 Tw');
                }
                $this->CellSmallCaps($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
            }
            else
            {
                if($align=='J')
                {
                    $this->ws=($ns>1) ? ($wmax-$ls)/1000*$this->FontSize/($ns-1) : 0;
                    $this->_out(sprintf('%.3F Tw',$this->ws*$this->k));
                }
                $this->CellSmallCaps($w,$h,substr($s,$j,$sep-$j),$b,2,$align,$fill);
                $i=$sep+1;
            }
            $sep=-1;
            $j=$i;
            $l=0;
            $ns=0;
            $nl++;
            if($border && $nl==2)
                $b=$b2;
        }
        else
            $i++;
    }
    //Last chunk
    if($this->ws>0)
    {
        $this->ws=0;
        $this->_out('0 Tw');
    }
    if($border && strpos($border,'B')!==false)
        $b.='B';
    $this->CellSmallCaps($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
    $this->x=$this->lMargin;
}

}//end of class
?>
