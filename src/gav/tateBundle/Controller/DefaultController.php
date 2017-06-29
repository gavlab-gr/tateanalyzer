<?php

namespace gav\tateBundle\Controller;

use gav\tateBundle\Entity\Artwork;
use gav\tateBundle\Entity\Subject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use gav\tateBundle\Entity\Artist;

class DefaultController extends Controller
{
    public function readSubjectsAction() {
        $em = $this->getDoctrine()->getManager();

        $allArt = $em->getRepository("gavtateBundle:Artwork")->findAll();

        foreach($allArt as $artwork) {
            $accession = $artwork->getAccession();
            $id = $artwork->getId();
            $letter = substr($accession,0,1);
            $second = substr($accession,1,3);
            $nameOfFile = $accession."-".$id.".json";
            $filename = "artwork/".$letter."/".$second."/".$nameOfFile;
            echo $filename;
            if (($handle = fopen($filename, "r")) !== FALSE) {
                $contents = fread($handle, filesize($filename));
                $json_a = json_decode($contents, true);
                print_r($json_a);
            }
            echo "finished";
            die();
        }
    }

    public function indexAction()
    {
        return $this->render('gavtateBundle:Default:index.html.twig');
    }

    public function artistAction(Request $request) {
        $form = $this->createFormBuilder()
            ->add('submitFile', 'file', array('label' => 'File to Submit'))
            ->getForm();

        // Check if we are posting stuff
        if ($request->getMethod('post') == 'POST') {
            // Bind request to the form
            $form->handleRequest($request);

            // If form is valid
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                // Get file
                $file = $form->get('submitFile');

                $data = $file->getData();

                    if (($handle = fopen($data, "r")) !== FALSE) {
                        $line = 0;
                        while(($row = fgetcsv($handle)) !== FALSE) {
                            if($line == 0) {
                                $line++;
                                continue;
                            }

                            $id = $row[0];
                            $name = $row[1];
                            $gender = $row[2];
                            $dates = $row[3];
                            $dob = $row[4];
                            $dod = $row[5];
                            $pob = $row[6];
                            $pod = $row[7];
                            $url = $row[8];

                            $art = new Artist($id, $name, $gender, $dob, $dod, $pob, $pod, $url);
                            $em->persist($art);
                        } // end of while
                        fclose($handle);
                    } // end of if FOPEN
                    $em->flush();
            }

        }

        return $this->render('gavtateBundle:Default:artist.html.twig',
            array('form' => $form->createView(),)
        );
    }

    public function artworkAction(Request $request) {
        $form = $this->createFormBuilder()
            ->add('submitFile', 'file', array('label' => 'File to Submit'))
            ->getForm();
        $line = 0;
        $counter = 0;
        // Check if we are posting stuff
        if ($request->getMethod('post') == 'POST') {
            // Bind request to the form
            $form->handleRequest($request);

            // If form is valid
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                // Get file
                $file = $form->get('submitFile');

                $data = $file->getData();

                    if (($handle = fopen($data, "r")) !== FALSE) {

                        while(($row = fgetcsv($handle)) !== FALSE) {
                            if($line == 0) {
                                $line++;
                                continue;
                            }
                            if($counter >= 5000)  {
                                $em->flush();
                                $counter = 0;
                            }

                            $id = $row[0];
                            $an = $row[1];
                            $artist = $row[2];
                            $role = $row[3];
                            $arid = $row[4];
                            $title = $row[5];
                            $datetext = $row[6];
                            $medium = $row[7];
                            $credit = $row[8];
                            $year = $row[9];
                            $acqyear = $row[10];
                            $dimensions = $row[11];
                            $width = $row[12];
                            $height = $row[13];
                            $depth = $row[14];
                            $units = $row[15];
                            $inscription = $row[16];
                            $thumbcopy = $row[17];
                            $thumb = $row[18];
                            $url = $row[19];

                            $artistObj = $em->getRepository("gavtateBundle:Artist")->find($arid);
                            $artworkObj = $em->getRepository("gavtateBundle:Artwork")->find($id);
                            if($artworkObj)
                                continue;
                            if($artistObj != null) {
                                $art = new Artwork($id, $an, $artistObj, $role, $title, $medium, $credit, $year, $acqyear, $url, $width, $height, $depth, $units);
                                $em->persist($art);
                                $counter++;
                            }

                        } // end of while
                        fclose($handle);
                    } // end of if FOPEN
                    $em->flush();
            }

        }

        return $this->render('gavtateBundle:Default:artwork.html.twig',
            array('form' => $form->createView(),'counter' => $counter)
        );
    }



    public function subjectAction(Request $request) {
        $form = $this->createFormBuilder()
            ->add('submitFile', 'file', array('label' => 'File to Submit'))
            ->getForm();
        $line = 0;
        $counter = 0;
        // Check if we are posting stuff
        if ($request->getMethod('post') == 'POST') {
            // Bind request to the form
            $form->handleRequest($request);

            // If form is valid
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                // Get file
                $file = $form->get('submitFile');

                $data = $file->getData();

                if (($handle = fopen($data, "r")) !== FALSE) {
                    $contents = fread($handle, filesize($data));
                    $json_a = json_decode($contents, true);

                    foreach($json_a as $key => $value) {
                        $id = $value['id'];
                        $name = $value['name'];
                        $parent0 = $value['parent0'];
                        $parent1 = $value['parent1'];
                        $parent0Obj = null;
                        $parent0Obj = null;
                        $level=0;

                        $subject = new Subject();
                        $subject->setId($id);
                        $subject->setTitle($name);
                        if($parent0 != "none") {
                            $parent0Obj = $em->getRepository("gavtateBundle:Subject")->find($parent0);
                            $level = 1;
                            $subject->setParent0($parent0Obj);
                        }
                        if($parent1 != "none") {
                            $parent1Obj = $em->getRepository("gavtateBundle:Subject")->find($parent0);
                            $level = 2;
                            $subject->setParent1($parent1Obj);
                        }
                        $subject->setLevel($level);
                        $em->persist($subject);
                    }
                    $em->flush();
                    fclose($handle);
                }
            }
        }

        return $this->render('gavtateBundle:Default:subject.html.twig',
            array('form' => $form->createView(),'counter' => $counter)
        );
    }


}
