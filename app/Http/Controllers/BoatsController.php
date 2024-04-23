<?php

namespace App\Http\Controllers;

use App\Models\BoatPapersModel;
use App\Models\DestinationModel;
use Illuminate\Http\Request;
use App\Models\BoatsModel;
use App\Models\OwnerModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Date;
use Carbon\Carbon;

// use BaconQrCode\Common\ErrorCorrectionLevel;
// use BaconQrCode\Encoder\QrCode;
// use BaconQrCode\Renderer\Image\Png;
// use BaconQrCode\Writer;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BoatsController extends Controller
{
    public function getAllBoats()
    {
        $results = BoatsModel::with(['type', 'papers', 'destination'])
            ->where('isActive', 1)
            ->orderBy('id', 'desc')
            ->get();

        return response([
            "status" => "success",
            "code" => "BIMSAPI0001", // success
            "data" => [
                "isProceed" => 1,
                "msg" => "Successfully fetched all boats data",
                'boatsData' => $results,
            ]
        ], 200);
    }

    public function getAllArchivedBoats()
    {
        $results = BoatsModel::with(['type', 'papers', 'destination'])
            ->where('isActive', 0)
            ->orderBy('id', 'desc')
            ->get();

        return response([
            "status" => "success",
            "code" => "BIMSAPI0001", // success
            "data" => [
                "isProceed" => 1,
                "msg" => "Successfully fetched all boats data",
                'boatsData' => $results,
            ]
        ], 200);
    }

    public function getBoatByID(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|max:10',
        ]);

        if ($validator->fails()) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0006", // validator fails
                'errors' => $validator->errors()
            ], 422);
        }

        $id = $request->input('id');

        $results = BoatsModel::with(['type', 'papers', 'destination'])
            ->where('id', $id)
            ->where('isActive', 1)
            ->first();

        return response([
            "status" => "success",
            "code" => "BIMSAPI0001", // success
            "data" => [
                "isProceed" => 1,
                "msg" => "Successfully fetched boat data",
                'boatsData' => $results,
            ]
        ], 200);
    }

    public function saveNewBoat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'boat_name' => 'required',
            'owner_name' => 'required',
            'type' => 'required|numeric',
            'nt' => 'required|numeric',
            'gt' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0006", // validator fails
                'errors' => $validator->errors()
            ], 422);
        }

        $checkBoatName = BoatsModel::where('boat_name', '=', $request->input('boat_name'))->get();

        if (count($checkBoatName)) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0011",
                'errors' => "Boat Already Exists"
            ], 422);
        }

        $boatsData = [
            'boat_name' => $request->input('boat_name'),
            'owner_name' => $request->input('owner_name'),
            'type' => $request->input('type'),
            'nt' => $request->input('nt'),
            'gt' => $request->input('gt'),
            'created_at' => now()->toDateString(),
        ];

        if ($request->input('type') == 1) {
            $validator->addRules([
                'length' => 'required|numeric',
                'breadth' => 'required|numeric',
                'depth' => 'required|numeric',
                'control_number' => 'required|numeric',
                'ocm' => 'required',
                'cap_name' => 'required',
            ]);

            if ($validator->fails()) {
                return response([
                    "status" => "failed",
                    "code" => "BIMSAPI0006", // validator fails
                    'errors' => $validator->errors()
                ], 422);
            }

            $boatsData['length'] = $request->input("length");
            $boatsData['breadth'] = $request->input("breadth");
            $boatsData['depth'] = $request->input("depth");
            $boatsData['control_number'] = $request->input("control_number");
            $boatsData['ocm'] = $request->input("ocm");
            $boatsData['cap_name'] = $request->input("cap_name");
        } else if ($request->input('type') == 2) {
            $validator->addRules([
                'cpr_rbc_date' => 'required|date_format:Y-m-d',
                'pssc_rbsc_date' => 'required|date_format:Y-m-d',
                'smc_date' => 'required|date_format:Y-m-d',
                'swl_date' => 'required|date_format:Y-m-d',
                'cwl_date' => 'required|date_format:Y-m-d',
                'lmc_date' => 'required|date_format:Y-m-d',
                'ssl_date' => 'required|date_format:Y-m-d',
                'p_ins' => 'required|date_format:Y-m-d',
                'p_cap' => 'required|numeric',
                'destination' => 'required',
            ]);

            if ($validator->fails()) {
                return response([
                    "status" => "failed",
                    "code" => "BIMSAPI0006", // validator fails
                    'errors' => $validator->errors()
                ], 422);
            }

            $boatPapersData = [
                'cpr_rbc_date' => $request->input('cpr_rbc_date'),
                'pssc_rbsc_date' => $request->input('pssc_rbsc_date'),
                'smc_date' => $request->input('smc_date'),
                'swl_date' => $request->input('swl_date'),
                'cwl_date' => $request->input('cwl_date'),
                'lmc_date' => $request->input('lmc_date'),
                'ssl_date' => $request->input('ssl_date'),
                'p_ins' => $request->input('p_ins'),
                'p_cap' => $request->input('p_cap'),
                'created_at' => now()->toDateString(),
            ];
        } else {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0011",
                'errors' => "Boat type is not valid"
            ], 422);
        }

        $boatSavedData = BoatsModel::create($boatsData);

        if ($request->input('type') == 2) {
            $boatPapersData['boat_id'] = $boatSavedData->id;

            $boatPapersSavedData = BoatPapersModel::create($boatPapersData);

            $destinationSavedData = DestinationModel::create(array(
                "boat_id" => $boatSavedData->id,
                "destination_desc" => $request->input('destination'),
                "created_at" => now()->toDateString()
            ));

            $response = array(
                "boatSavedData" => $boatSavedData,
                "boatPapersSavedData" => $boatPapersSavedData,
                "destinationSavedData" => $destinationSavedData,
            );

            return $response;
        }

        return response([
            "status" => "success",
            "code" => "BIMSAPI0008",
            "msg" => "Saving new boat succcessful",
            "data" => $boatSavedData,
        ], 200);
    }

    public function deleteBoat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'boat_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0006", // validator fails
                'errors' => $validator->errors()
            ], 422);
        }

        $boat_info = BoatsModel::find($request->input('boat_id'));
        $destination_info = DestinationModel::where('boat_id', $request->input('boat_id'))->first();
        $papers_info = BoatPapersModel::where('boat_id', $request->input('boat_id'))->first();

        if (!$boat_info) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0012",
                "message" => "No such Boat ID",
            ], 401);
        }

        // $boat_info->update([
        //     'isActive' => 0
        // ]);

        if ($boat_info->type == 2) {
            $destination_info->delete();
            $papers_info->delete();
        }

        $boat_info->delete();

        return response([
            "status" => "success",
            "code" => "BIMSAPI0008",
            "message" => "Record deleted successfully",
        ], 200);
    }

    public function updateBoat(Request $request)
    {
        $BoatsModel = new BoatsModel;
        $DestinationModel = new DestinationModel;
        $BoatPapersModel = new BoatPapersModel;

        $validator = Validator::make($request->all(), [
            'boat_id' => 'required|max:10',
            'cols_to_change' => 'required|array|min:1',
            'cols_values' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0006", // validator fails
                'errors' => $validator->errors()
            ], 422);
        }

        $id = $request->input('boat_id');

        $boat_info = BoatsModel::find($request->input('boat_id'));
        $destination_info = DestinationModel::where('boat_id', $request->input('boat_id'))->first();
        $papers_info = BoatPapersModel::where('boat_id', $request->input('boat_id'))->first();

        if (!$boat_info) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0012",
                "message" => "No such Boat ID",
            ], 401);
        }

        $col_BoatsModel = $BoatsModel->getConnection()->getSchemaBuilder()->getColumnListing('boat_tbl');
        $col_DestinationModel = $DestinationModel->getConnection()->getSchemaBuilder()->getColumnListing('destination_tbl');
        $col_BoatPapersModel = $BoatPapersModel->getConnection()->getSchemaBuilder()->getColumnListing('boat_papers_tbl');

        $boatsUpdate = [];
        $DestinationUpdate = [];
        $papersUpdate = [];


        for ($i = 0; $i < count($request->input("cols_to_change")); $i++) {
            if (in_array($request->input("cols_to_change")[$i], $col_BoatsModel)) {
                $boatsUpdate[$request->input("cols_to_change")[$i]] = $request->input("cols_values")[$i];
            }

            if (in_array($request->input("cols_to_change")[$i], $col_DestinationModel)) {
                $DestinationUpdate[$request->input("cols_to_change")[$i]] = $request->input("cols_values")[$i];
            }

            if (in_array($request->input("cols_to_change")[$i], $col_BoatPapersModel)) {
                $papersUpdate[$request->input("cols_to_change")[$i]] = $request->input("cols_values")[$i];
            }
        }

        // return response([
        //     "boatsUpdate" => $boatsUpdate,
        //     "DestinationUpdate" => $DestinationUpdate,
        //     "papersUpdate" => $papersUpdate,
        //     "type" => $boat_info->type
        // ]);

        $boat_info->update($boatsUpdate);

        if ($boat_info->type == 2) {
            $destination_info->update($DestinationUpdate);
            $papers_info->update($papersUpdate);
        }

        return response([
            "status" => "success",
            "code" => "BIMSAPI0013",
            "message" => "Record updated successfully",
        ], 200);
    }

    public function generateBoatQRCode(Request $request)
    {
        $BoatsModel = new BoatsModel;

        $validator = Validator::make($request->all(), [
            'boat_id' => 'required|max:10',
        ]);

        if ($validator->fails()) {
            return response([
                "status" => "failed",
                "code" => "BIMSAPI0006", // validator fails
                'errors' => $validator->errors()
            ], 422);
        }

        $id = $request->input('boat_id');


        $content = $id;
        $content = [
            "id" => $id,
            "verification" => "BIMS_QR_CODE",
        ];

        // Generate the QR code image
        $qrCode = QrCode::size(300)->generate(json_encode($content));

        echo $qrCode;



        // return response([
        //     "status" => "success",
        //     "code" => "BIMSAPI0013",
        //     "message" => "Record updated successfully",
        //     "data" => [
        //         'id' => $id
        //     ]
        // ], 200);
    }

    public function generateQRCode(Request $request)
    {
        // Your logic to fetch content based on the ID
        $content = "13";

        // Generate the QR code image
        $qrCode = QrCode::size(300)->generate($content);

        echo $qrCode;
    }

    public function getExpiredPapers(Request $request)
    {
        $results = BoatsModel::with(['papers'])->where('type', 2)->orderBy('id', 'desc')->get();

        // return $results;
        // $currentDate = Carbon::now();

        $oneMonthBefore = Carbon::now()->format('Y-m-d H:i:s');
        $expiredDates = [];

        // return $oneMonthBefore;

        foreach ($results as $boatKey => $boat) {
            $cpr_rbc_date = Carbon::parse($boat->papers->cpr_rbc_date);
            $pssc_rbsc_date = Carbon::parse($boat->papers->pssc_rbsc_date);
            $smc_date = Carbon::parse($boat->papers->smc_date);
            $swl_date = Carbon::parse($boat->papers->swl_date);
            $cwl_date = Carbon::parse($boat->papers->cwl_date);
            $lmc_date = Carbon::parse($boat->papers->lmc_date);
            $ssl_date = Carbon::parse($boat->papers->ssl_date);
            $p_ins = Carbon::parse($boat->papers->p_ins);


            if ($cpr_rbc_date<=$oneMonthBefore) {
                array_push($expiredDates,array(
                    "cpr_rbc_date" => $cpr_rbc_date,
                    "boatInfo" => array(
                        'id' => $boat->id,
                        "boat_name" => $boat->boat_name
                    )
                ));
            }

            if ($pssc_rbsc_date<=$oneMonthBefore) {
                array_push($expiredDates,array(
                    "pssc_rbsc_date" => $pssc_rbsc_date,
                    "boatInfo" => array(
                        'id' => $boat->id,
                        "boat_name" => $boat->boat_name
                    )
                ));
            }

            if ($smc_date<=$oneMonthBefore) {
                array_push($expiredDates,array(
                    "smc_date" => $smc_date,
                    "boatInfo" => array(
                        'id' => $boat->id,
                        "boat_name" => $boat->boat_name
                    )
                ));
            }

            if ($swl_date<=$oneMonthBefore) {
                array_push($expiredDates,array(
                    "swl_date" => $swl_date,
                    "boatInfo" => array(
                        'id' => $boat->id,
                        "boat_name" => $boat->boat_name
                    )
                ));
            }

            if ($cwl_date<=$oneMonthBefore) {
                array_push($expiredDates,array(
                    "swl_date" => $cwl_date,
                    "boatInfo" => array(
                        'id' => $boat->id,
                        "boat_name" => $boat->boat_name
                    )
                ));
            }

            if ($lmc_date<=$oneMonthBefore) {
                array_push($expiredDates,array(
                    "lmc_date" => $lmc_date,
                    "boatInfo" => array(
                        'id' => $boat->id,
                        "boat_name" => $boat->boat_name
                    )
                ));
            }

            if ($ssl_date<=$oneMonthBefore) {
                array_push($expiredDates,array(
                    "ssl_date" => $ssl_date,
                    "boatInfo" => array(
                        'id' => $boat->id,
                        "boat_name" => $boat->boat_name
                    )
                ));
            }

            if ($p_ins<=$oneMonthBefore) {
                array_push($expiredDates,array(
                    "p_ins" => $p_ins,
                    "boatInfo" => array(
                        'id' => $boat->id,
                        "boat_name" => $boat->boat_name
                    )
                ));
            }


            

            

            
        }

        return response([
            "status" => "success",
            "code" => "BIMSAPI0001", // success
            "data" => [
                "isProceed" => 1,
                "msg" => "Successfully fetched  data",
                'expiredDates' => $expiredDates,
            ]
        ], 200);
    }
}
