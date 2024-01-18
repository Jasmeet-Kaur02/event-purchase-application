<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use stdClass;
use App\Models\User;

class EventController extends Controller
{
    public function create(Request $request)
    {
        $validatedData = customValidate($request->all(), [
            'title' => "required|string",
            "description" => 'required|string',
            "dateTime" => 'required|date',
            "venue" => "required|string",
            "ticketPrize" => "required|integer|min:1",
            "totalSeats" => "required|integer"
        ]);

        $event = Event::create($validatedData);
        $data =  new stdClass;
        $data->event = $event;

        return $this->success($data, 'Event has been created successfully', 200);
    }

    public function update(Request $request, $eventId)
    {

        $validatedData = customValidate(array_merge($request->all(), ['eventId' => $eventId]), [
            'title' => "nullable|string",
            "description" => "nullable|string",
            "dateTime" => "nullable|date",
            "venue" => "required|string",
            "ticketPrize" => "nullable|integer",
            "totalSeats" => "nullable|integer",
            'eventId' => "required|integer|exists:events,id"
        ]);

        $event = Event::find($validatedData['eventId']);

        foreach ($validatedData as $key => $value) {
            if (!is_null($value) && $key !== 'eventId') {
                $event->$key = $value;
            }
        }

        $event->save();
        $data = new stdClass;
        $data->event = $event;

        return $this->success($data, "Event has been updated successfully", 200);
    }

    public function delete($eventId)
    {
        customValidate(['eventId' => $eventId], [
            'eventId' => "required|integer|exists:events,id"
        ]);
        $event = Event::find($eventId);
        $event->delete();

        return $this->success(true, "Event has been delete successfully", 200);
    }

    public function get()
    {
        $events = Event::all();
        $data = new stdClass;
        $data->events = $events;

        return $this->success($data, "All events have been fetched successfully", 200);
    }

    public function purchaseEvent($userId, $eventId)
    {
        customValidate(['eventId' => $eventId], [
            'eventId' => "required|integer|exists:events,id"
        ]);
        validateUserId($userId);
        $amount = 5 * 100;
        $user = User::find($userId);
        $wallet = $user->wallet;
        $balance = $wallet->balance;

        if (($balance / 100) < 5) {
            return $this->error("You don't have a sufficient balance to purchase event", 400);
        }

        $wallet->balance = $balance - $amount;
        $wallet->save();
        $user->events()->attach($eventId);

        return $this->success(true, "You have purchased event ticket successfully", 200);
    }
}
