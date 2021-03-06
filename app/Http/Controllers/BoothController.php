<?php namespace App\Http\Controllers;

use App\Booth;
use App\Candidate;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Utilities\Youtube;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class BoothController extends Controller
{
    public function __construct()
    {
        //學生會限定
        $this->middleware('sa');

        Validator::extend('youtube', function ($field, $value, $parameters) {
            return Youtube::isYoutube($value);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $user = Auth::user();
        $boothList = Booth::orderBy('id', 'asc')->get();
        return view('booth.list')->with('boothList', $boothList);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('booth.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),
            array(
                'name' => 'required|max:20',
                'url' => 'youtube|max:255'
            )
        );

        if ($validator->fails()) {
            return Redirect::route('booth.create')
                ->withErrors($validator)
                ->withInput();
        } else {
            $booth = Booth::create(array(
                'name' => $request->get('name'),
                'url' => $request->get('url')
            ));

            Log::info('[Vote] ' . Auth::user()->id . ' ' . Auth::user()->name . ' 新增了投票所：' . $booth->name, [
                'id' => $booth->id,
                'name' => $booth->name,
                'url' => $booth->url
            ]);

            return Redirect::route('booth.show', $booth->id)
                ->with('global', '投票所資料已更新');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        $user = Auth::user();
        $booth = Booth::find($id);
        if ($booth) {
            //科系（依出現順序）
            $departmentArray = Candidate::orderBy('id', 'asc')->distinct('department')->select('department')->get()->toArray();
            $departmentList = [];
            foreach ($departmentArray as $department) {
                $departmentList[] = $department['department'];
            }
            foreach ($departmentList as &$value) {
                $value = "'$value'";
            }
            $departments = implode(',', $departmentList);
            foreach (Config::get('vote.type') as $voteType) {
                //取得候選人清單
                if ($voteType == '學生會會長') {
                    $candidateList[$voteType] = Candidate::where('type', '=', $voteType)
                        ->orderBy('number', 'asc')
                        ->get();
                } else {
                    $candidateList[$voteType] = Candidate::where('type', '=', $voteType)
                        ->orderByRaw(DB::raw("FIELD(department, " . $departments . ")"))
                        ->orderBy('number', 'asc')
                        ->get();
                }
            }
            return view('booth.show')->with('user', $user)->with('booth', $booth)->with('candidateList', $candidateList);
        }
        return Redirect::route('booth.index')
            ->with('warning', '投票所不存在');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id)
    {
        $booth = Booth::find($id);
        if ($booth) {
            return view('booth.edit')->with('booth', $booth);
        }
        return Redirect::route('booth.index')
            ->with('warning', '投票所不存在');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $booth = Booth::find($id);
        if (!$booth) {
            return Redirect::route('booth.index')
                ->with('warning', '投票所不存在');
        }

        $validator = Validator::make($request->all(),
            array(
                'name' => 'required|max:20',
                'url' => 'youtube|max:255'
            )
        );

        if ($validator->fails()) {
            return Redirect::route('booth.edit', $id)
                ->withErrors($validator)
                ->withInput();
        } else {
            $booth->name = $request->get('name');
            $booth->url = $request->get('url');
            $booth->save();

            Log::info('[Vote] ' . Auth::user()->id . ' ' . Auth::user()->name . ' 更新了投票所：' . $booth->name, [
                'id' => $booth->id,
                'name' => $booth->name,
                'url' => $booth->url
            ]);

            return Redirect::route('booth.show', $id)
                ->with('global', '投票所資料已更新');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        $booth = Booth::find($id);
        if ($booth) {
            Log::info('[Vote] ' . Auth::user()->id . ' ' . Auth::user()->name . ' 刪除了投票所：' . $booth->name, [
                'id' => $booth->id,
                'name' => $booth->name,
                'url' => $booth->url
            ]);

            $booth->delete();
            return Redirect::route('booth.index')
                ->with('global', '投票所已刪除');
        }
        return Redirect::route('booth.index')
            ->with('warning', '投票所不存在');
    }

}
