<?php

require_once '../../config.php'; // to include $CFG, for example
require_once($CFG->libdir . '/csvlib.class.php');

global $CFG, $DB, $COURSE;

$dataformat = required_param('documenttype', PARAM_ALPHA);
$hittrainingsnachweisid = optional_param('hittrainingsnachweisid', '', PARAM_INT);
$studentid = optional_param('studentid', '', PARAM_INT);
$cmid = required_param('cmid',  PARAM_INT); // Course Module ID.
if ($cmid) {
    $cm = get_coursemodule_from_id('hittrainingsnachweis', $cmid, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $hittrainingsnachweis = $DB->get_record('hittrainingsnachweis', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    $hittrainingsnachweis = $DB->get_record('hittrainingsnachweis', array('id' => $bid), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('hittrainingsnachweis', $hittrainingsnachweis->id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $cmid = $cm->id;
}

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/hittrainingsnachweis:read', $context);
require_capability('mod/hittrainingsnachweis:edit', $context);

if($dataformat == 'csv'){
    $downloadfilename = clean_filename("trainingsnachweise_csv");
    $csvexport = new csv_export_writer ('semicolon');
    $csvexport->set_filename($downloadfilename);
    $userdata = $DB->get_records_sql(
        "SELECT 
                    a.id,
                    a.training_dttm, a.beschreibung, 
                    case when a.abgezeichnet = 'Y' then 'Ja' else 'Nein' end as abgezeichnet,
                    case 
                        when a.bewertung = 1 then 'sehr gut'
                        when a.bewertung = 2 then 'gut'
                        when a.bewertung = 3 then 'neutral'
                        when a.bewertung = 4 then 'schlecht'
                        when a.bewertung = 5 then 'sehr schlecht'
                        else 'nicht bewertet'
                    end as bewertung,
                    CONCAT(u.firstname, ' ', u.lastname) as abzeichner
                  from {hittrainingsnachweis_entry} as a
                    left join mdl_user as u on a.abzeichnerid=u.id
                  where hittrainingsnachweisid = :hittnid and studentid = :studentid",
        array( "hittnid" => $hittrainingsnachweis->id, "studentid" => $studentid));
    $fieldnames = array(
        'id',
        'training_dttm',
        'beschreibung',
        'abgezeichnet',
        'bewertung',
        'abzeichner'
    );
    $exporttitle = array();
    foreach ($fieldnames as $field) {
        $exporttitle [] = $field;
    }
    $csvexport->add_data($exporttitle);
    $userdata = array_values($userdata);
    foreach($userdata as $userdataitem){
        $userdataitem = (array)$userdataitem;
        foreach(array_keys($userdataitem) as $key){
           if($key == 'training_dttm'){
               $userdataitem[$key] = date('Y-m-d H:i:s',$userdataitem[$key]);
            }
        }
        $csvexport->add_data($userdataitem);
    }
    $csvexport->download_file();
} else if($dataformat == 'pdf'){

    $userdata = $DB->get_records_sql(
        "SELECT 
                    a.id as id,
                    a.training_dttm, a.beschreibung, 
                    case when a.abgezeichnet = 'Y' then 'Ja' else 'Nein' end as abgezeichnet,
                    case 
                        when a.bewertung = 1 then 'sehr gut'
                        when a.bewertung = 2 then 'gut'
                        when a.bewertung = 3 then 'neutral'
                        when a.bewertung = 4 then 'schlecht'
                        when a.bewertung = 5 then 'sehr schlecht'
                        else 'nicht bewertet'
                    end as bewertung,
                    case 
                        when a.bewertung = 1 then 'https://www.herolditservice.de/wp-content/uploads/2022/01/smilie-sehrgut.png'
                        when a.bewertung = 2 then 'https://www.herolditservice.de/wp-content/uploads/2022/01/smilie-gut.png'
                        when a.bewertung = 3 then 'https://www.herolditservice.de/wp-content/uploads/2022/01/smilie-neutral.png'
                        when a.bewertung = 4 then 'https://www.herolditservice.de/wp-content/uploads/2022/01/smilie-schlecht.png'
                        when a.bewertung = 5 then 'https://www.herolditservice.de/wp-content/uploads/2022/01/smilie-sehrschlecht.png'
                        else 'https://www.herolditservice.de/wp-content/uploads/2022/01/smilie-none.png'
                    end as simly_link,
                    a.bewertung as bewertung_nr,
                    CONCAT(u.firstname, ' ', u.lastname) as abzeichner,
                    CONCAT(az.firstname, '', az.lastname) as azubi
                  from {hittrainingsnachweis_entry} as a
                    left join mdl_user as u on a.abzeichnerid=u.id
                    left join mdl_user as az on a.studentid=az.id
                  where hittrainingsnachweisid = :hittnid and studentid = :studentid",
        array( "hittnid" => $hittrainingsnachweis->id, "studentid" => $studentid));
    $userdatatransformed = array();
    foreach($userdata as $userdataitem){
        $userdataitem = (array)$userdataitem;
        foreach(array_keys($userdataitem) as $key){
            if($key == 'id'){
                $userdataitem[$key] = "test";
            }
            unset($userdataitem['id']);
            if($key == 'training_dttm'){
                $userdataitem[$key] = date('Y-m-d',$userdataitem[$key]);
            }
        }
        array_push($userdatatransformed, $userdataitem);
    }

    require('./thirdparty/fpdf/fpdf.php');

    class PDF extends FPDF
    {

        // Page footer
        function Footer()
        {
            // Position at 1.5 cm from bottom
            $this->SetY(-15);
            // Arial italic 8
            $this->SetFont('Arial','I',8);
            // Page number
            $this->Cell(0,10,'Seite '.$this->PageNo().'',0,0,'C');
        }
        function BasicTable($header, $data, $hittrainingsnachweis)
        {

            // Header
            $this->Cell(10,7,'',1);
            $this->Cell(30,7,$header[0],1);
            $this->Cell(60,7,$header[1],1);
            $this->Cell(40,7,$header[2],1);
            $this->Cell(40,7,$header[3],1);
            $this->Cell(15,7,$header[4],1);

            $this->Ln();
            // Data
            $i = 1;
            foreach($data as $row) {
                $this->Cell(10,15 ,$i,1);
                $this->Cell(30,15 ,$row['training_dttm'],1);
                #$this->Multicell(60,15,$hittrainingsnachweis->name,1);
                $this->Cell(60,15, "6".$hittrainingsnachweis->name, 1);
                $this->Cell(40,15,$row['azubi'],1);
                $this->Cell(40,15,$row['abzeichner'],1);
                //$this->Cell(15,15,$row['bewertung_nr'],1);
                $this->Cell(15,15,$this->Image($row['simly_link'], $this->GetX()+2, $this->GetY()+2, 10),1);
                $this->Ln();
                $i++;
            }
        }
    }

    class PDF_MC_Table extends FPDF
    {
        var $widths;
        var $aligns;

        function SetWidths($w)
        {
            //Set the array of column widths
            $this->widths=$w;
        }

        function SetAligns($a)
        {
            //Set the array of column alignments
            $this->aligns=$a;
        }

        function HeadRow($data)
        {
            //Calculate the height of the row
            $nb=0;

            $h=5*2.8;
            //Issue a page break first if needed
            $this->CheckPageBreak($h);
            $fieldnames = array(
                "",
                'Datum',
                'Training, Modell-Name',
                'Azubi',
                'Abzeichner',
                'Note'
            );
            $this->SetFont('Arial','B',15);
            for($i=0;$i<6;$i++){
                $w=$this->widths[$i];
                $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
                //Save the current position
                $x=$this->GetX();
                $y=$this->GetY();
                //Draw the border
                $this->Rect($x,$y,$w,$h);
                //Print the text
                $this->MultiCell($w,10,$fieldnames[$i],0,$a);
                //Put the position to the right of the cell
                $this->SetXY($x+$w,$y);
            }
            $this->Ln($h);
            //Go to the next line
        }

        function Row($data)
        {
            //Calculate the height of the row
            $nb=0;
            for($i=0;$i<count($data);$i++){
                if($i == 5){
                    $nb=max($nb,2.8);
                } else {
                    $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
                }
            }

            $h=5*$nb;
            //Issue a page break first if needed
            $this->CheckPageBreak($h);
            //Draw the cells of the row
            $this->SetFont('Arial','',13);
            for($i=0;$i<count($data);$i++)
            {
                if($i == 5){
                    $w=$this->widths[$i];
                    $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
                    //Save the current position
                    $x=$this->GetX();
                    $y=$this->GetY();
                    //Draw the border
                    $this->Rect($x,$y,$w,$h);
                    //Print the text
                    $this->MultiCell($w,5,$this->Image($data[$i], $this->GetX()+2, $this->GetY()+2, 10),0,$a);
                    //Put the position to the right of the cell
                    $this->SetXY($x+$w,$y);
                } else {
                    $w=$this->widths[$i];
                    $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
                    //Save the current position
                    $x=$this->GetX();
                    $y=$this->GetY();
                    //Draw the border
                    $this->Rect($x,$y,$w,$h);
                    //Print the text
                    $this->MultiCell($w,5,$data[$i],0,$a);
                    //Put the position to the right of the cell
                    $this->SetXY($x+$w,$y);
                }

            }
            //Go to the next line
            $this->Ln($h);
        }

        function CheckPageBreak($h)
        {
            //If the height h would cause an overflow, add a new page immediately
            if($this->GetY()+$h>$this->PageBreakTrigger)
                $this->AddPage($this->CurOrientation);
        }

        function NbLines($w,$txt)
        {
            //Computes the number of lines a MultiCell of width w will take
            $cw=&$this->CurrentFont['cw'];
            if($w==0)
                $w=$this->w-$this->rMargin-$this->x;
            $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
            $s=str_replace("\r",'',$txt);
            $nb=strlen($s);
            if($nb>0 and $s[$nb-1]=="\n")
                $nb--;
            $sep=-1;
            $i=0;
            $j=0;
            $l=0;
            $nl=1;
            while($i<$nb)
            {
                $c=$s[$i];
                if($c=="\n")
                {
                    $i++;
                    $sep=-1;
                    $j=$i;
                    $l=0;
                    $nl++;
                    continue;
                }
                if($c==' ')
                    $sep=$i;
                $l+=$cw[$c];
                if($l>$wmax)
                {
                    if($sep==-1)
                    {
                        if($i==$j)
                            $i++;
                    }
                    else
                        $i=$sep+1;
                    $sep=-1;
                    $j=$i;
                    $l=0;
                    $nl++;
                }
                else
                    $i++;
            }
            return $nl;
        }
    }

    $pdf = new PDF_MC_Table();
    $pdf->SetFont('Arial','',14);
    $pdf->AddPage();

    // Logo
    $pdf->Image('https://lamp-frisuren.de/wp-content/uploads/2013/11/HairBeautyArtist.jpg',170,6,30);
    // Arial bold 15
    $pdf->SetFont('Arial','B',15);
    // Move to the right
    //$pdf->Cell(10);
    // Title
    $pdf->Cell(10,10,'MODELL-TRAININGSNACHWEIS ',10,10,'L');
    // Line break
    $pdf->Ln(20);

    $pdf->SetFont('Arial','', 15);

    $pdf->Cell(40,10,iconv('UTF-8', 'ISO-8859-1','Thema: '.$hittrainingsnachweis->name));
    $pdf->Ln();
    $student = $DB->get_record('user', array('id' => $studentid), '*', MUST_EXIST);
    $pdf->Cell(40,10,'Auszubildener: '.$student->firstname." ".$student->lastname);
    $pdf->Cell(100,10,'');
    $pdf->Cell(40,10,'Geplante Anzahl: '.$hittrainingsnachweis->wiederholungen);
    $pdf->Ln();
    $pdf->SetWidths(array(5,30,62,40,45,15));

    $pdf->HeadRow(array(
        "","","","","",""
    ));
    for($i=0;$i<sizeof($userdatatransformed);$i++){
      $pdf->Row(array(
          $i+1,
          iconv('UTF-8', 'ISO-8859-1',$userdatatransformed[$i]['training_dttm']),
          iconv('UTF-8', 'ISO-8859-1',$hittrainingsnachweis->name),
          iconv('UTF-8', 'ISO-8859-1',$userdatatransformed[$i]['azubi']),
          iconv('UTF-8', 'ISO-8859-1',$userdatatransformed[$i]['abzeichner']),
          iconv('UTF-8', 'ISO-8859-1',$userdatatransformed[$i]['simly_link'])
      ));
    }
    $pdf->Output();


} else {
    $courseurl = new moodle_url('/mod/hittrainingsnachweis/view_studententries.php',
        array('cmid' => $cm->id, 'studentid' => $studentid, 'message' => 'error'));
    redirect($courseurl);
}
