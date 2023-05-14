<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Trips extends Public_Controller{

    public function __construct(){
        parent::__construct();
        $this->load->model('trips_m');
        $this->load->model('students/students_m');

    }


    public function move_corinates_to_archives(){

        $journey = $this->trips_m->get_cordinates_to_archive();
        //print_r($journey); 
        if($journey){
            $cordinates = $this->trips_m->get_cordinates_by_journey_id($journey->id);
            if(!empty($cordinates)){
                $ids_to_delete = [];
                $archive_cordinates = [];
                foreach ($cordinates as $key => $cordinate) {
                    $ids_to_delete[] = $cordinate->id;
                    $archive_cordinates[] = [
                        'old_journey_cordinates_id' => $cordinate->id,
                        'journey_id' => $cordinate->journey_id,
                        'trip_id' => $cordinate->trip_id,
                        'vehicle_id' => $cordinate->vehicle_id,
                        'driver_id' => $cordinate->driver_id,
                        'route_id' => $cordinate->route_id,
                        'school_id' => $cordinate->school_id,
                        'longitude' => $cordinate->longitude,
                        'latitude' => $cordinate->latitude,
                        'description' => $cordinate->description,
                        'active' => $cordinate->active,
                        'created_by' => $cordinate->created_by,
                        'created_on' => $cordinate->created_on,
                        'modified_on' => $cordinate->modified_on,
                        'modified_by' => $cordinate->modified_by,
                        'distance' => $cordinate->distance,
                        'distance_value' => $cordinate->distance_value,
                        'duration' => $cordinate->duration,
                        'duration_value' => $cordinate->duration_value,
                        'accuracy' => $cordinate->accuracy,
                        'speed' => $cordinate->speed,
                        'speed_accuracy' => $cordinate->speed_accuracy,
                        'journey_time' => $cordinate->journey_time,
                        'vertical_accuracy' => $cordinate->vertical_accuracy,
                        'heading' => $cordinate->heading
                    ];
                }
                if($this->trips_m->insert_old_journey_batch($archive_cordinates)){
                    
                    if(count($ids_to_delete) > 0){
                        $this->trips_m->delete_cordinatess_in_bulk($ids_to_delete);
                        echo count($ids_to_delete) ." inserted <br>";
                    }
                }
            }else{
                echo $journey->id ." Marked as archived <br>";
                $update = [
                    'is_archived' => 1,
                    'modified_on' => time()
                ];
                $this->trips_m->update_journey($journey->id, $update);
            }
            print_r($journey);
            print_r($cordinates);
        }else{
            $update = [
                'is_archived' => 1,
                'modified_on' => time()
            ];
            //$this->trips_m->update_journey($journey->id, $update);
        }
    }


    public function end_student_past_inactive_journeys(){
        $active_journey = $this->students_m->get_past_not_active_journeys();
        //print_r($active_journey); die();
        $status = 0;
        foreach ($active_journey as $key => $journey) {
            $status++;
            $input_journey = array(
                'is_journey_end'=> 1,
                //'is_onborded'=> 2,
                'on_end_longitude'=>$journey->longitude,
                'on_end_latitude'=>$journey->latitude,
                'modified_on'=> time(),
                'modified_by'=> 1
            );
            $this->students_m->update_student_journey($journey->id,$input_journey);
        }
        echo $status;
    }
}