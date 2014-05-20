<?php

class AdminController extends Controller {

	public $crumb;

	public $model;

	public $heads;

	public $fields;

	public $collection;

	public $controller_name;

	public $form;

	public $form_framework = 'TwitterBootstrap';

	public $form_class = 'form-horizontal';

	public $validator = array();

	public $actions = '';

	public $form_add = 'new';

	public $form_edit = 'edit';

	public $view_object = 'view';

	public $title = '';

    public $ajaxsource = null;

    public $addurl = null;

    public $importurl = null;

    public $importkey = null;

    public $rowdetail = null;

    public $delurl = null;

    public $dlxl = null;

    public $newbutton = null;

    public $backlink = '';

    public $makeActions = 'makeActions';

    public $can_add = true;

    public $is_report = false;

    public $report_action = '';

    public $is_additional_action = false;

    public $additional_action = '';

    public $additional_filter = '';

    public $js_additional_param = '';

    public $additional_query = false;

    public $def_order_by = 'lastUpdate';

    public $def_order_dir = 'desc';

    public $place_action = 'both'; // first, both

    public $table_view = 'tables.simple';

	public function __construct(){

		date_default_timezone_set('Asia/Jakarta');

		Former::framework($this->form_framework);

        //$this->crumb = new \Noherczeg\Breadcrumb\Breadcrumb(URL::to('/'));

		$this->beforeFilter('auth', array('on'=>'get', 'only'=>array('getIndex','getAdd','getEdit') ));

        $this->backlink = strtolower($this->controller_name);

        Logger::access();

	}


	/**
	 * Setup the layout used by the controller.
	 *
	 * @return void
	 */
	protected function setupLayout()
	{
		if ( ! is_null($this->layout))
		{
			$this->layout = View::make($this->layout);
		}
	}

    public function getIndex()
    {
        return $this->pageGenerator();
    }

    public function postIndex()
    {
        return $this->tableResponder();
    }

	public function pageGenerator(){

		//$action_selection = Former::select( Config::get('kickstart.actionselection'))->name('action');

		$heads = $this->heads;

        $this->ajaxsource = (is_null($this->ajaxsource))? strtolower($this->controller_name): $this->ajaxsource;

        $this->addurl = (is_null($this->addurl))? strtolower($this->controller_name).'/add': $this->addurl;

        $this->importurl = (is_null($this->importurl))? strtolower($this->controller_name).'/import': $this->importurl;

        $this->rowdetail = (is_null($this->rowdetail))? strtolower($this->controller_name).'.rowdetail': $this->rowdetail;

        $this->delurl = (is_null($this->delurl))? strtolower($this->controller_name).'/del': $this->delurl;

        $this->newbutton = (is_null($this->newbutton))? Str::singular($this->controller_name): $this->newbutton;

		$select_all = Former::checkbox()->name('Select All')->check(false)->id('select_all');

		// add selector and sequence columns
        if($this->place_action == 'both' || $this->place_action == 'first'){
            array_unshift($heads, array('Actions',array('sort'=>false,'class'=>'action')));
        }
		array_unshift($heads, array($select_all,array('sort'=>false)));
		array_unshift($heads, array('#',array('sort'=>false)));

		// add action column
        if($this->place_action == 'both'){
            array_push($heads,
                array('Actions',array('search'=>false,'sort'=>false,'clear'=>true,'class'=>'action'))
            );
        }

		$disablesort = array();

		for($s = 0; $s < count($heads);$s++){
			if($heads[$s][1]['sort'] == false){
				$disablesort[] = $s;
			}
		}

		$disablesort = implode(',',$disablesort);

        /* additional features */

        $this->dlxl = (is_null($this->dlxl))? strtolower($this->controller_name).'/dlxl': $this->dlxl;


		return View::make($this->table_view)
			->with('title',$this->title )
			->with('newbutton', $this->newbutton )
			->with('disablesort',$disablesort )
			->with('addurl',$this->addurl )
            ->with('importurl',$this->importurl )
			->with('ajaxsource',URL::to($this->ajaxsource) )
			->with('ajaxdel',URL::to($this->delurl) )
            ->with('ajaxdlxl',URL::to($this->dlxl) )
			->with('crumb',$this->crumb )
            ->with('can_add', $this->can_add )
            ->with('is_report',$this->is_report)
            ->with('report_action',$this->report_action)
            ->with('is_additional_action',$this->is_additional_action)
            ->with('additional_action',$this->additional_action)
            ->with('additional_filter',$this->additional_filter)
            ->with('js_additional_param', $this->js_additional_param)
			->with('heads',$heads )
			->with('row',$this->rowdetail );


	}


	public function tableResponder()
	{

		$fields = $this->fields;

        $count_all = 0;
        $count_display_all = 0;

		//print_r($fields);

		//array_unshift($fields, array('select',array('kind'=>false)));
		array_unshift($fields, array('seq',array('kind'=>false)));
        if($this->place_action == 'both' || $this->place_action == 'first'){
            array_unshift($fields, array('action',array('kind'=>false)));
        }

		$pagestart = Input::get('iDisplayStart');
		$pagelength = Input::get('iDisplayLength');

		$limit = array($pagelength, $pagestart);

		$defsort = 1;
		$defdir = -1;

		$idx = 0;
		$q = array();

		$hilite = array();
		$hilite_replace = array();

		for($i = 0;$i < count($fields);$i++){
			$idx = $i;

			//print_r($fields[$i]);

			$field = $fields[$i][0];
			$type = $fields[$i][1]['kind'];

			$qval = '';

			if(Input::get('sSearch_'.$i))
			{
				if( $type == 'text'){
					if($fields[$i][1]['query'] == 'like'){
						$pos = $fields[$i][1]['pos'];
						if($pos == 'both'){
							//$model->whereRegex($field,'/'.Input::get('sSearch_'.$idx).'/i');
                            //$this->model->where($field,'like','%'.Input::get('sSearch_'.$idx).'%');

							$qval = new MongoRegex('/'.Input::get('sSearch_'.$idx).'/i');
						}else if($pos == 'before'){
							//$this->model->whereRegex($field,'/^'.Input::get('sSearch_'.$idx).'/i');
                            //$this->model->where($field,'like','%'.Input::get('sSearch_'.$idx));

							$qval = new MongoRegex('/^'.Input::get('sSearch_'.$idx).'/i');
						}else if($pos == 'after'){
							//$this->model->whereRegex($field,'/'.Input::get('sSearch_'.$idx).'$/i');
                            //$this->model->where($field,'like', Input::get('sSearch_'.$idx).'%');

							$qval = new MongoRegex('/'.Input::get('sSearch_'.$idx).'$/i');
						}
					}else{
						$qval = Input::get('sSearch_'.$idx);

						//$this->model->where($field,$qval);
					}

                    $q[$field] = $qval;

				}elseif($type == 'numeric' || $type == 'currency'){
					$str = Input::get('sSearch_'.$idx);

					$sign = null;

					$strval = trim(str_replace(array('<','>','='), '', $str));

					$qval = (double)$strval;

					/*
					if(is_null($sign)){
						$qval = new MongoInt32($strval);
					}else{
						$str = new MongoInt32($str);
						$qval = array($sign=>$str);
					}
					*/


					if(strpos($str, "<=") !== false){
						$sign = '$lte';

						//$this->model->whereLte($field,$qval);
                        //$this->model->where($field,'<=',$qval);

					}elseif(strpos($str, ">=") !== false){
						$sign = '$gte';

						//$this->model->whereGte($field,$qval);
                        //$this->model->where($field,'>=',$qval);

					}elseif(strpos($str, ">") !== false){
						$sign = '$gt';

						//$this->model->whereGt($field,$qval);
                        //$this->model->where($field,'>',$qval);

					}elseif(stripos($str, "<") !== false){
						$sign = '$lt';

						//$this->model->whereLt($field,$qval);
                        //$this->model->where($field,'<',$qval);

					}

					//print $sign;
                    if(!is_null($sign)){
                        $qval = array($sign=>$qval);
                    }

                    $q[$field] = $qval;

				}elseif($type == 'date'|| $type == 'datetime'){
					$datestring = Input::get('sSearch_'.$idx);
                    $datestring = date('d-m-Y', $datestring / 1000);

					if (($timestamp = $datestring) === false) {
					} else {
						$daystart = new MongoDate(strtotime($datestring.' 00:00:00'));
						$dayend = new MongoDate(strtotime($datestring.' 23:59:59'));

						$qval = array($field =>array('$gte'=>$daystart,'$lte'=>$dayend));
					    //echo "$str == " . date('l dS \o\f F Y h:i:s A', $timestamp);

						//$this->model->whereBetween($field,$daystart,$dayend);

					}
					$qval = array('$gte'=>$daystart,'$lte'=>$dayend);
					//$qval = Input::get('sSearch_'.$idx);

                    $q[$field] = $qval;

				}elseif($type == '__datetime'){
					$datestring = Input::get('sSearch_'.$idx);

                    print $datestring;

					$qval = new MongoDate(strtotime($datestring));

					//$this->model->where($field,$qval);
                    $q[$field] = $qval;

				}


			}

		}

        if($this->additional_query){
            $q = array_merge( $q, $this->additional_query );
        }

		//print_r($q);


		/* first column is always sequence number, so must be omitted */

		$fidx = Input::get('iSortCol_0') - 1;

		$fidx = ($fidx == -1 )?0:$fidx;

        if(Input::get('iSortCol_0') == 0){
            $sort_col = $this->def_order_by;

            $sort_dir = $this->def_order_dir;
        }else{
            $sort_col = $fields[$fidx][0];

            $sort_dir = Input::get('sSortDir_0');

        }


		/*
		if(count($q) > 0){
			$results = $model->skip( $pagestart )->take( $pagelength )->orderBy($sort_col, $sort_dir )->get();
			$count_display_all = $model->count();
		}else{
			$results = $model->find(array(),array(),array($sort_col=>$sort_dir),$limit);
			$count_display_all = $model->count();
		}
		*/

        //$model->where('docFormat','picture');

        $count_all = $this->model->count();
        $count_display_all = $this->model->count();

        if(is_array($q) && count($q) > 0){
            $results = $this->model->whereRaw($q)->skip( $pagestart )->take( $pagelength )->orderBy($sort_col, $sort_dir )->get();

            $count_display_all = $this->model->whereRaw($q)->count();

        }else{
            $results = $this->model->skip( $pagestart )->take( $pagelength )->orderBy($sort_col, $sort_dir )->get();

            $count_display_all = $this->model->count();

        }

        //print_r($results->toArray());

		$aadata = array();

		$form = $this->form;

		$counter = 1 + $pagestart;

		foreach ($results as $doc) {

			$extra = $doc;

			//$select = Former::checkbox('sel_'.$doc['_id'])->check(false)->id($doc['_id'])->class('selector');
            $actionMaker = $this->makeActions;

			$actions = $this->$actionMaker($doc);

			$row = array();

			$row[] = $counter;

			//$sel = Former::checkbox('sel_'.$doc['_id'])->check(false)->label(false)->id($doc['_id'])->class('selector')->__toString();
			$sel = '<input type="checkbox" name="sel_'.$doc['_id'].'" id="'.$doc['_id'].'" value="'.$doc['_id'].'" class="selector" />';
			$row[] = $sel;

            if($this->place_action == 'both' || $this->place_action == 'first'){
                $row[] = $actions;
            }


			foreach($fields as $field){
				if($field[1]['kind'] != false && $field[1]['show'] == true){

					$fieldarray = explode('.',$field[0]);
					if(is_array($fieldarray) && count($fieldarray) > 1){
						$fieldarray = implode('\'][\'',$fieldarray);
						$cstring = '$label = (isset($doc[\''.$fieldarray.'\']))?true:false;';
						eval($cstring);
					}else{
						$label = (isset($doc[$field[0]]))?true:false;
					}


					if($label){

						if( isset($field[1]['callback']) && $field[1]['callback'] != ''){
							$callback = $field[1]['callback'];
							$row[] = $this->$callback($doc, $field[0]);
						}else{
							if($field[1]['kind'] == 'datetime'){
                                if($doc[$field[0]] instanceof MongoDate){
                                    $rowitem = date('d-m-Y H:i:s',$doc[$field[0]]->sec);
                                }elseif ($doc[$field[0]] instanceof Date) {
                                    $rowitem = date('d-m-Y H:i:s',$doc[$field[0]]);
                                }else{
                                    //$rowitem = $doc[$field[0]];
                                    if(is_array($doc[$field[0]])){
                                        $rowitem = date('d-m-Y H:i:s', time() );
                                    }else{
                                        $rowitem = date('d-m-Y H:i:s',strtotime($doc[$field[0]]) );
                                    }
                                }
							}elseif($field[1]['kind'] == 'date'){
                                if($doc[$field[0]] instanceof MongoDate){
                                    $rowitem = date('d-m-Y',$doc[$field[0]]->sec);
                                }elseif ($doc[$field[0]] instanceof Date) {
                                    $rowitem = date('d-m-Y',$doc[$field[0]]);
                                }else{
                                    //$rowitem = $doc[$field[0]];
                                    $rowitem = date('d-m-Y',strtotime($doc[$field[0]]) );
                                }
							}elseif($field[1]['kind'] == 'currency'){
								$num = (double) $doc[$field[0]];
								$rowitem = number_format($num,2,',','.');
							}else{
								$rowitem = $doc[$field[0]];
							}

							if(isset($field[1]['attr'])){
								$attr = '';
								foreach ($field[1]['attr'] as $key => $value) {
									$attr .= $key.'="'.$value.'" ';
								}
								$row[] = '<span '.$attr.' >'.$rowitem.'</span>';
							}else{
								$row[] = $rowitem;
							}

						}


					}else{
						$row[] = '';
					}
				}
			}

            if($this->place_action == 'both'){
                $row[] = $actions;
            }

			$row['extra'] = $extra;

			$aadata[] = $row;

			$counter++;
		}

        $sEcho = (int) Input::get('sEcho');

		$result = array(
			'sEcho'=>  $sEcho,
			'iTotalRecords'=>$count_all,
			'iTotalDisplayRecords'=> (is_null($count_display_all))?0:$count_display_all,
			'aaData'=>$aadata,
			'qrs'=>$q,
			'sort'=>array($sort_col=>$sort_dir)
		);

		return Response::json($result);
	}

	public function getAdd(){

		$controller_name = strtolower($this->controller_name);

		//$this->crumb->add($controller_name.'/add','New '.Str::singular($this->controller_name));
        $data = $this->beforeAddForm();

		$model = $this->model;

		$form = $this->form;

        $title = ($this->title == '')?Str::singular($this->controller_name):$this->title;

		return View::make($controller_name.'.'.$this->form_add)
					->with('back',$controller_name)
                    ->with('auxdata',$data)
					->with('form',$form)
					->with('submit',$controller_name.'/add')
					->with('crumb',$this->crumb)
					->with('title','New '.$title);

	}

	public function postAdd($data = null){

		//print_r(Session::get('permission'));
		if(is_null($data)){
			$data = Input::get();
		}

		//print_r($data);

		$data = $this->beforeValidateAdd($data);

		$controller_name = strtolower($this->controller_name);

        $this->backlink = ($this->backlink == '')?$controller_name:$this->backlink;

	    $validation = Validator::make($input = $data, $this->validator);

	    if($validation->fails()){

	    	return Redirect::to($controller_name.'/add')->withErrors($validation)->withInput(Input::all());

	    }else{

			unset($data['csrf_token']);

			$data['createdDate'] = new MongoDate();
			$data['lastUpdate'] = new MongoDate();


			$model = $this->model;


			$data = $this->beforeSave($data);

			if($obj = $model->insert($data)){

				$obj = $this->afterSave($data);

				//Event::fire('product.createformadmin',array($obj['_id'],$passwordRandom,$obj['conventionPaymentStatus']));
		    	return Redirect::to($this->backlink)->with('notify_success',ucfirst(Str::singular($controller_name)).' saved successfully');
			}else{
		    	return Redirect::to($this->backlink)->with('notify_success',ucfirst(Str::singular($controller_name)).' saving failed');
			}


	    }

	}

	public function getEdit($id){

		$controller_name = strtolower($this->controller_name);

		//$this->crumb->add(strtolower($this->controller_name).'/edit','Edit',false);

		//$model = $this->model;

		$_id = new MongoId($id);

		//$population = $model->where('_id',$_id)->first();

        $population = $this->model->find($id)->toArray();

		$population = $this->beforeUpdateForm($population);

		foreach ($population as $key=>$val) {
			if($val instanceof MongoDate){
				$population[$key] = date('d-m-Y H:i:s',$val->sec);
			}
		}

		//print_r($population);

		//exit();

		Former::populate($population);

		//$this->crumb->add(strtolower($this->controller_name).'/edit/'.$id,$id,false);

        $title = ($this->title == '')?Str::singular($this->controller_name):$this->title;

		return View::make(strtolower($this->controller_name).'.'.$this->form_edit)
					->with('back',$controller_name)
					->with('formdata',$population)
					->with('submit',strtolower($this->controller_name).'/edit/'.$id)
					->with('title','Edit '.$title);

	}


	public function postEdit($id,$data = null){

		$controller_name = strtolower($this->controller_name);
		//print_r(Session::get('permission'));

        $this->backlink = ($this->backlink == '')?$controller_name:$this->backlink;

	    $validation = Validator::make($input = Input::all(), $this->validator);

	    if($validation->fails()){

	    	return Redirect::to($controller_name.'/edit/'.$id)->withInput(Input::all())->withErrors($validation);
	    	//->with_input(Input::all());

	    }else{

	    	if(is_null($data)){
				$data = Input::get();
	    	}

			$id = new MongoId($data['id']);
			$data['lastUpdate'] = new MongoDate();

			unset($data['csrf_token']);
			unset($data['id']);

			//print_r($data);
			//exit();

			$model = $this->model;

			$data = $this->beforeUpdate($id,$data);

			if($obj = $model->where('_id',$id)->update($data)){

				$obj = $this->afterUpdate($id,$data);
				if($obj != false){
			    	return Redirect::to($this->backlink)->with('notify_success',ucfirst(Str::singular($controller_name)).' saved successfully');
				}
			}else{
		    	return Redirect::to($this->backlink)->with('notify_success',ucfirst(Str::singular($controller_name)).' saving failed');
			}

	    }

	}



	public function postDel(){
		$id = Input::get('id');

		$controller_name = strtolower($this->controller_name);

		$model = $this->model;

		if(is_null($id)){
			$result = array('status'=>'ERR','data'=>'NOID');
		}else{

			$id = new MongoId($id);

			if($model->where('_id',$id)->delete()){
				Event::fire($controller_name.'.delete',array('id'=>$id,'result'=>'OK'));
				$result = array('status'=>'OK','data'=>'CONTENTDELETED');
			}else{
				Event::fire($controller_name.'.delete',array('id'=>$id,'result'=>'FAILED'));
				$result = array('status'=>'ERR','data'=>'DELETEFAILED');
			}
		}

		return Response::json($result);
	}

	public function beforeSave($data)
	{
		return $data;
	}

	public function afterSave($data)
	{
		return $data;
	}

	public function makeActions($data){
		return '';
	}

	public function beforeUpdate($id,$data)
	{
		return $data;
	}

	public function afterUpdate($id,$data = null)
	{
		return $id;
	}

	public function beforeView($data)
	{
		return $data;
	}

	public function beforeValidateAdd($data)
	{
		return $data;
	}

    public function beforeAddForm()
    {
        return null;
    }

	public function beforeUpdateForm($population)
	{
		if(isset($population['tags']) && is_array($population['tags']))
		{
			$population['tags'] = implode(',', $population['tags'] );
		}
		return $population;
	}

    public function completeHeads($heads){

        $select_all = Former::checkbox()->name('Select All')->check(false)->id('select_all');

        //product head
        array_unshift($heads, array($select_all,array('search'=>false,'sort'=>false)));
        array_unshift($heads, array('#',array('search'=>false,'sort'=>false)));
        array_push($heads,
            array('Actions',array('search'=>false,'sort'=>false,'clear'=>true))
        );

        return $heads;
    }

	public function get_view($id){
		$_id = new MongoId($id);

		$model = $this->model;

		$obj = $model->where('_id',$_id)->get();

		$obj = $this->beforeView($obj);

		$this->crumb->add(strtolower($this->controller_name).'/view/'.$id,'View',false);
		$this->crumb->add(strtolower($this->controller_name).'/view/'.$id,$id,false);

		//return View::make(strtolower($this->controller_name).'.'.$this->view_object)
		return View::make('view')
			->with('obj',$obj);
	}

    public function postDlxl()
    {

        $fields = $this->fields; // fields set must align with search column index

        if(is_null($this->heads)){
            $titles = array();
            foreach ($this->fields as $fh) {
                $titles[] = array(ucwords($fh[0]),array('search'=>true,'sort'=>true));
            }
        }else{
            $titles = $this->heads;
        }

        //print_r($titles);

        array_unshift($fields, array('seq',array('kind'=>false)));
        array_unshift($fields, array('action',array('kind'=>false)));

        array_unshift($titles, array('seq',array('kind'=>false)));
        array_unshift($titles, array('action',array('kind'=>false)));

        $infilters = Input::get('filter');
        $insorting = Input::get('sort');

        //print_r($infilters);
        //print_r($fields);

        $defsort = 1;
        $defdir = -1;

        $idx = 0;
        $q = array();

        $hilite = array();
        $hilite_replace = array();

        $colheads = array();

        //exit();

        for($i = 0;$i < count($fields);$i++){
            $idx = $i;

            //print_r($fields[$i]);

            $field = $fields[$i][0];
            $title = $titles[$i][0];
            $type = $fields[$i][1]['kind'];

            $colheads[$i] = $field;
            $coltitles[$i] = $title;

            $qval = '';

            //print 'filter : '. $field.' : '.$infilters[$i];

            if( isset($infilters[$i]) && $infilters[$i])
            {
                if( $type == 'text'){
                    if($fields[$i][1]['query'] == 'like'){
                        $pos = $fields[$i][1]['pos'];
                        if($pos == 'both'){
                            //$model->whereRegex($field,'/'.Input::get('sSearch_'.$idx).'/i');
                            //$this->model->where($field,'like','%'.Input::get('sSearch_'.$idx).'%');

                            $qval = new MongoRegex('/'.$infilters[$i].'/i');
                        }else if($pos == 'before'){
                            //$this->model->whereRegex($field,'/^'.$infilters[$i].'/i');
                            //$this->model->where($field,'like','%'.$infilters[$i]);

                            $qval = new MongoRegex('/^'.$infilters[$i].'/i');
                        }else if($pos == 'after'){
                            //$this->model->whereRegex($field,'/'.$infilters[$i].'$/i');
                            //$this->model->where($field,'like', $infilters[$i].'%');

                            $qval = new MongoRegex('/'.$infilters[$i].'$/i');
                        }
                    }else{
                        $qval = $infilters[$i];

                        //$this->model->where($field,$qval);
                    }

                    $q[$field] = $qval;

                }elseif($type == 'numeric' || $type == 'currency'){
                    $str = $infilters[$i];

                    $sign = null;

                    $strval = trim(str_replace(array('<','>','='), '', $str));

                    $qval = (double)$strval;

                    /*
                    if(is_null($sign)){
                        $qval = new MongoInt32($strval);
                    }else{
                        $str = new MongoInt32($str);
                        $qval = array($sign=>$str);
                    }
                    */


                    if(strpos($str, "<=") !== false){
                        $sign = '$lte';

                        //$this->model->whereLte($field,$qval);
                        //$this->model->where($field,'<=',$qval);

                    }elseif(strpos($str, ">=") !== false){
                        $sign = '$gte';

                        //$this->model->whereGte($field,$qval);
                        //$this->model->where($field,'>=',$qval);

                    }elseif(strpos($str, ">") !== false){
                        $sign = '$gt';

                        //$this->model->whereGt($field,$qval);
                        //$this->model->where($field,'>',$qval);

                    }elseif(stripos($str, "<") !== false){
                        $sign = '$lt';

                        //$this->model->whereLt($field,$qval);
                        //$this->model->where($field,'<',$qval);

                    }

                    //print $sign;
                    if(!is_null($sign)){
                        $qval = array($sign=>$qval);
                    }

                    $q[$field] = $qval;

                }elseif($type == 'date'|| $type == 'datetime'){
                    $datestring = $infilters[$i];
                    $datestring = date('d-m-Y', $datestring / 1000);

                    if (($timestamp = $datestring) === false) {
                    } else {
                        $daystart = new MongoDate(strtotime($datestring.' 00:00:00'));
                        $dayend = new MongoDate(strtotime($datestring.' 23:59:59'));

                        $qval = array($field =>array('$gte'=>$daystart,'$lte'=>$dayend));
                        //echo "$str == " . date('l dS \o\f F Y h:i:s A', $timestamp);

                        //$this->model->whereBetween($field,$daystart,$dayend);

                    }
                    $qval = array('$gte'=>$daystart,'$lte'=>$dayend);
                    //$qval = $infilters[$i];

                    $q[$field] = $qval;

                }elseif($type == '__datetime'){
                    $datestring = $infilters[$i];

                    print $datestring;

                    $qval = new MongoDate(strtotime($datestring));

                    //$this->model->where($field,$qval);
                    $q[$field] = $qval;

                }


            }

        }

        //print_r($q);

        /*
        if(count($q) > 0){
            $results = $model->skip( $pagestart )->take( $pagelength )->orderBy($sort_col, $sort_dir )->get();
            $count_display_all = $model->count();
        }else{
            $results = $model->find(array(),array(),array($sort_col=>$sort_dir),$limit);
            $count_display_all = $model->count();
        }
        */

        //$model->where('docFormat','picture');

        array_unshift($fields, array('sel',array('kind'=>false)));

        if($insorting[0] == 0){
            $sort_col = 'lastUpdate';

            $sort_dir = 'desc';
        }else{
            $sort_col = $fields[$insorting[0]][0];

            $sort_dir = $insorting[1];

        }

        //print $sort_col.' -> '.$sort_dir;



        if(is_array($q) && count($q) > 0){
            $results = $this->model->whereRaw($q)->orderBy($sort_col, $sort_dir )->get();

            $count_display_all = $this->model->whereRaw($q)->count();

        }else{
            $results = $this->model->orderBy($sort_col, $sort_dir )->get();

            $count_display_all = $this->model->count();

        }

        $lastQuery = $q;

        //print_r($results->toArray());

        $aadata = array();

        $counter = 1;

        foreach ($results as $doc) {

            $row = array();

            //$row[] = $counter;

            foreach($fields as $field){
                if($field[1]['kind'] != false && $field[1]['show'] == true){

                    $fieldarray = explode('.',$field[0]);
                    if(is_array($fieldarray) && count($fieldarray) > 1){
                        $fieldarray = implode('\'][\'',$fieldarray);
                        $cstring = '$label = (isset($doc[\''.$fieldarray.'\']))?true:false;';
                        eval($cstring);
                    }else{
                        $label = (isset($doc[$field[0]]))?true:false;
                    }


                    if($label){

                        if( isset($field[1]['callback']) && $field[1]['callback'] != ''){
                            $callback = $field[1]['callback'];
                            $row[] = $this->$callback($doc, $field[0]);
                        }else{
                            if($field[1]['kind'] == 'datetime'){
                                if($doc[$field[0]] instanceof MongoDate){
                                    $rowitem = date('d-m-Y H:i:s',$doc[$field[0]]->sec);
                                }elseif ($doc[$field[0]] instanceof Date) {
                                    $rowitem = date('d-m-Y H:i:s',$doc[$field[0]]);
                                }else{
                                    //$rowitem = $doc[$field[0]];
                                    if(is_array($doc[$field[0]])){
                                        $rowitem = date('d-m-Y H:i:s', time() );
                                    }else{
                                        $rowitem = date('d-m-Y H:i:s',strtotime($doc[$field[0]]) );
                                    }
                                }
                            }elseif($field[1]['kind'] == 'date'){
                                if($doc[$field[0]] instanceof MongoDate){
                                    $rowitem = date('d-m-Y',$doc[$field[0]]->sec);
                                }elseif ($doc[$field[0]] instanceof Date) {
                                    $rowitem = date('d-m-Y',$doc[$field[0]]);
                                }else{
                                    //$rowitem = $doc[$field[0]];
                                    $rowitem = date('d-m-Y',strtotime($doc[$field[0]]) );
                                }
                            }elseif($field[1]['kind'] == 'currency'){
                                $num = (double) $doc[$field[0]];
                                $rowitem = number_format($num,2,',','.');
                            }else{
                                $rowitem = $doc[$field[0]];
                            }

                            if(isset($field[1]['attr'])){
                                $attr = '';
                                foreach ($field[1]['attr'] as $key => $value) {
                                    $attr .= $key.'="'.$value.'" ';
                                }
                                $row[] = '<span '.$attr.' >'.$rowitem.'</span>';
                            }else{
                                $row[] = $rowitem;
                            }

                        }


                    }else{
                        $row[] = '';
                    }
                }
            }

            $aadata[] = $row;

            $counter++;
        }

        $sdata = $aadata;

        array_shift($colheads);
        array_shift($colheads);
        array_shift($coltitles);
        array_shift($coltitles);

        array_unshift($sdata,$colheads);
        array_unshift($sdata,$coltitles);

        //print_r($sdata);
        //print public_path();

        $fname =  $this->controller_name.'_'.date('d-m-Y-H-m-s',time());

        /*
        Excel::create( $fname )
            ->sheet('sheet1')
            ->with($sdata)
            ->save('xls',public_path().'/storage/dled');
        */

        Excel::create( $fname )
            ->sheet('sheet1')
            ->with($sdata)
            ->save('xls',public_path().'/storage/dled');

        $fp = fopen(public_path().'/storage/dled/'.$fname.'.csv', 'w');

        foreach ($sdata as $fields) {
            fputcsv($fp, $fields, ',' , '"');
        }

        fclose($fp);


        $result = array(
            'status'=>'OK',
            'filename'=>$fname,
            'urlxls'=>URL::to(strtolower($this->controller_name).'/dl/'.$fname.'.xls'),
            'urlcsv'=>URL::to(strtolower($this->controller_name).'/csv/'.$fname.'.csv'),
            'q'=>$lastQuery
        );

        print json_encode($result);

    }

    public function getDl($filename)
    {
        $dlfile = public_path().'/storage/dled/'.$filename;

        $headers = array(
                'Content-Type: application/vnd.ms-excel'
            );
        return Response::download($dlfile, $filename, $headers );
    }

    public function getCsv($filename)
    {
        $dlfile = public_path().'/storage/dled/'.$filename;

        $headers = array(
                'Content-Type: text/csv'
            );
        return Response::download($dlfile, $filename, $headers );
    }

    public function getImport()
    {
        return View::make('shared.importinput')
            ->with('title',$this->title)
            //->with('input_name',$this->input_name)
            ->with('importkey', $this->importkey)
            ->with('back',strtolower($this->controller_name))
            ->with('submit',strtolower($this->controller_name).'/uploadimport');
    }

    public function postUploadimport()
    {
        $file = Input::file('inputfile');

        $headindex = Input::get('headindex');

        $firstdata = Input::get('firstdata');

        $importkey = (!is_null($this->importkey))?Input::get('importkey'):$this->importkey;

        //$importkey = $this->importkey;

        $rstring = str_random(15);

        $destinationPath = realpath('storage/upload').'/'.$rstring;

        $filename = $file->getClientOriginalName();
        $filemime = $file->getMimeType();
        $filesize = $file->getSize();
        $extension =$file->getClientOriginalExtension(); //if you need extension of the file

        $filename = str_replace(Config::get('kickstart.invalidchars'), '-', $filename);

        $uploadSuccess = $file->move($destinationPath, $filename);

        $fileitems = array();

        if($uploadSuccess){

            $xlsfile = realpath('storage/upload').'/'.$rstring.'/'.$filename;

            $imp = Excel::load($xlsfile)->toArray();

            $headrow = $imp[$headindex - 1];

            $firstdata = $firstdata - 1;

            $imported = array();

            //print_r($headrow);

            for($i = $firstdata; $i < count($imp);$i++){

                $row = $imp[$i];

                $rowitem = array();

                for($j = 0 ; $j < count($headrow); $j++){
                    if(isset($headrow[$j])){
                        $rowitem[$headrow[$j]] = $row[$j];
                    }
                }

                $imported[] = $rowitem;

                if($importkey != '' && !is_null($importkey)){
                    $obj = $this->model->where($importkey, '=', $rowitem[$importkey])->first();

                    if($obj){

                        foreach($rowitem as $k=>$v){
                            if($v != ''){
                                $obj->{$k} = $v;
                            }
                        }

                        $obj->save();
                    }else{

                        $rowitem['createdDate'] = new MongoDate();
                        $rowitem['lastUpdate'] = new MongoDate();

                        $rowitem = $this->beforeImportCommit($rowitem);

                        $this->model->insert($rowitem);
                    }


                }else{

                    $rowitem['createdDate'] = new MongoDate();
                    $rowitem['lastUpdate'] = new MongoDate();

                    $rowitem = $this->beforeImportCommit($rowitem);

                    $this->model->insert($rowitem);

                }

            }


        }

        return Redirect::to($this->backlink);

    }

    public function beforeImportCommit($rowitem)
    {
        return $rowitem;
    }



	public function get_action_sample(){
		\Laravel\CLI\Command::run(array('notify'));
	}

    public function missingMethod($param = array())
    {
        //print_r($param);
    }

}