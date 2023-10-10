<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use App\Repositories\Outcomes;

use Illuminate\Support\Facades\DB;

class OutcomesController extends Controller
{
    protected $outcomes;

    public function __construct(Outcomes $outcomes)
    {
        $this->outcomes = $outcomes;

    }

    public function index()
    {

      $listaperiodos = $this->outcomes->buscarperiodo();
      $todosperiodos = array($listaperiodos);
      return view('admin/outcomes',  compact('todosperiodos'));
      
    }

    public function listarcursos(Request $request)
    {
      $listacursos = $this->outcomes->buscarcurso($request->selectPeriodo);
      $todoscursos = json_encode($listacursos);
      return $todoscursos;

    }
    
    public function assigmentdetalle($courseId,$assigmentId)
    {

          $resultados = array();
          $rubricas = $this->listarsrubrica($courseId, $assigmentId);

          foreach ($rubricas as $rubrica) {

            $resultado = array();
            $coursename = $rubrica->course->name;
            $coursecode = $rubrica->course->course_code;
            $userid = $rubrica->user_id;
            $username = $rubrica->user->name;
            $userlogin = $rubrica->user->login_id;
            $assigmentid = $rubrica->assignment->id;
            $assigmentname = $rubrica->assignment->name;




            $rubricAssessmentData = [];
            $sumaPuntos = 0;
            for($i = 1; $i<=5; $i++){

              /*$resultado['rubricId_'.$i] ="";
              $resultado['rubricComent_'.$i] ="";*/
              $resultado['rubricPoints_'.$i] ="";

            }
            // Verifica si rubric_assessment existe antes de intentar recorrerlo
            if (isset($rubrica->rubric_assessment)) {
              $i=0;
              foreach ($rubrica->rubric_assessment as $rubricId => $rubricData) {
                $i++;
                
                /*$ratingId = $rubricData->rating_id;
                $comments = $rubricData->comments;*/
                //$points = $rubricData->points;
                $points = isset($rubricData->points) ? $rubricData->points : 0;
                /*$resultado['rubricId_'.$i] =$ratingId;
                $resultado['rubricComent_'.$i] =$comments;*/
                $resultado['rubricPoints_'.$i] =$points;
                $sumaPuntos += $points;

              }

            }


            if ($sumaPuntos >= 81 && $sumaPuntos <= 100) {
            $categoria = "Exceeds Mastery";
            } elseif ($sumaPuntos >= 61 && $sumaPuntos <= 80) {
            $categoria = "Mastery";
            } elseif ($sumaPuntos >= 41 && $sumaPuntos <= 60) {
            $categoria = "Near Mastery";
            } elseif ($sumaPuntos >= 21 && $sumaPuntos <= 40) {
            $categoria = "Below Mastery";
            } elseif ($sumaPuntos >= 0 && $sumaPuntos <= 20) {
            $categoria = "Well Below Mastery";
            } else {
            $categoria = "Categoría no válida"; // Maneja el caso en el que la suma de puntos está fuera de los rangos esperados
            }

      
            $grade = isset($rubrica->grade) ? $rubrica->grade : 0;
            $score = isset($rubrica->score) ? $rubrica->score : 0;
      
            
            $resultado['coursename'] = $coursename;
            $resultado['userid'] = $userid;
            $resultado['grade'] = $grade;
            $resultado['score'] = $score;
            $resultado['username'] = $username;
            $resultado['userlogin'] = $userlogin;
            $resultado['assigmentid'] = $assigmentid;
            $resultado['assigmentname'] = $assigmentname;
            $resultado['categoria'] =$categoria;


            $resultados3 = array();
            $buscarcanvasg = $this->outcomes->cursobusqueda($courseId);

            $buscarbasedatos = DB::select("SELECT 
            oc.id AS course_id, 
            oc.curso, 
            oc.short_name AS short_name, 
            ot.id AS task_id, 
            ot.tarea, 
            ot.T1,
            ot.T2,
            ot.T3,
            CASE WHEN ot.T1 = 1 THEN oc.descrip1 ELSE 'No' END AS dest1,
            CASE WHEN ot.T2 = 2 THEN oc.descrip2 ELSE 'No' END AS dest2,
            CASE WHEN ot.T3 = 3 THEN oc.descrip3 ELSE 'No' END AS dest3
            FROM outcomes_courses oc
            INNER JOIN outcomes_tasks ot ON oc.id = ot.curso_id
            WHERE oc.short_name = '{$buscarcanvasg->course_code}'");


            if (empty($buscarbasedatos)) {
              $resultados3['buscarbasedatos'] = "No se encontraron resultados de Outcomes";
            }else{
                $string = "Week 2 Assignment";
                if (preg_match('/(\d+)/', $string, $matches)) {
                $numero = $matches[0];
                $numtarea = $numero;
                } else {
                $numtarea = "no hay ninguna coincidencia";
                }

                $elemento = $buscarbasedatos[0];
                
                
                if (strpos($elemento->tarea, $numtarea) !== false) {

                  $resultados3['elementocurso'] = $elemento->curso;
                  $resultados3['elementoshortname'] = $elemento->short_name;
                  $resultados3['elementotask_id'] =  $elemento->task_id;
                  $resultados3['elementotasktarea'] =  $elemento->tarea;
                  $resultados3['elementot1'] =  $elemento->T1;
                  $resultados3['elementot2'] =  $elemento->T2;
                  $resultados3['elementot3'] =  $elemento->T3;

                  $resultados3['elementot1d'] =  $elemento->dest1;
                  $resultados3['elementot2d'] =  $elemento->dest2;
                  $resultados3['elementot3d'] =  $elemento->dest3;
                  
          
                }else{
                  dd("no esite");
                }
            
            }                        

            $resultado = array_merge($resultado, $resultados3);
            $resultados[] = $resultado;
          }


          $result['rubricas'] = $resultados;
       
          dd($resultados);

      return view('admin/listarstudents', ['resultados' => $resultados]);
    }


    public function listarsrubrica($courseid, $actividadid)
    {
        $listarubrica = $this->outcomes->listsrubrica($courseid, $actividadid); // Usar $courseid y $actividadid aquí
        $todorubrica = json_decode(json_encode($listarubrica));
       //dd($todorubrica);
        return $todorubrica;
    }

    public function prueba()
    {
      return view('admin/prueba');

    }

    public function assigmentxcourse($course){

      $listatareas = $this->outcomes->buscaridactividad($course);
      $todostareas = json_decode(json_encode($listatareas));

      //dd($todostareas);

      if (empty($todostareas)) {
        $mensaje = "No hay tareas disponibles.";
        return view('admin/listassigment', compact('mensaje'));
      }

      else{

        foreach ($todostareas as $item) {
          $resultado = array();

          $courseid = $item->course_id;
          $tareaid = $item->id;
          $tareadue_at = $item->due_at;
          $tareaunlock_at = $item->unlock_at;
          $tareaname = $item->name;
          $tareaworkflow_state = $item->workflow_state;

          if (isset($item->rubric_settings)) {
            $rubricSettings = $item->rubric_settings;
            } else {
            $rubricSettings = 0; 
            }
          
          $resultado['courseid'] = $courseid;
          $resultado['tareaid'] = $tareaid;
          $resultado['tareadue_at'] = $tareadue_at;
          $resultado['tareaunlock_at'] = $tareaunlock_at;
          $resultado['tareaname'] = $tareaname;
          $resultado['tareaworkflow_state'] = $tareaworkflow_state;
          $resultado['rubricSettings'] = $rubricSettings;

          $resultados[] = $resultado;
          
        }

    
        return view('admin/listassigment', ['resultados' => $resultados]);



      }


    }

    



}
