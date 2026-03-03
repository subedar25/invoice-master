<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Requests\ClientRequest;
use App\Models\Client;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
class ClientCrudController extends Controller
{
    public function setup()
    {
        CRUD::setModel(Client::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/client');
        CRUD::setEntityNameStrings('client', 'clients');
    }

    protected function setupListOperation()
    {
        CRUD::column('name');
        CRUD::column('phone');
        CRUD::column('contact_name');
        CRUD::column('contact_email');
        CRUD::column('open')->type('boolean');
        CRUD::column('active')->type('boolean');
        CRUD::column('contact_preference')->type('enum');
        CRUD::column('billing_preference')->type('enum');
        CRUD::column('marketing_preferences')->type('enum');
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(ClientRequest::class);

        CRUD::field('name')->type('text');
        CRUD::field('phone')->type('text');

        CRUD::field('contact_name');
        CRUD::field('contact_email_nickname');
        CRUD::field('contact_email')->type('email');
        CRUD::field('contact_phone_office');
        CRUD::field('contact_phone_mobile');

        CRUD::field('billing_contact_name');
        CRUD::field('billing_contact_email_nickname');
        CRUD::field('billing_contact_email')->type('email');
        CRUD::field('billing_contact_phone_office');
        CRUD::field('billing_contact_phone_phone');

        CRUD::field('address_line1')->type('textarea');
        CRUD::field('address_line2')->type('textarea');
        CRUD::field('address_city');
        CRUD::field('address_state');
        CRUD::field('address_zipcode');
        CRUD::field('address_country');

        CRUD::field('contact_preference')->type('select_from_array')->options([
            'email' => 'Email',
            'phone' => 'Phone',
            'text' => 'Text',
            'no_preference' => 'No Preference',
            'see_notes' => 'See Notes',
        ]);

        CRUD::field('billing_preference')->type('select_from_array')->options([
            'email' => 'Email',
            'mail' => 'Mail',
        ]);

        CRUD::field('type');
        CRUD::field('innline_types');
        CRUD::field('innline_amenities');

        CRUD::field('open')->type('checkbox');
        CRUD::field('active')->type('checkbox');

        CRUD::field('primary_contact_id')->type('number');
        CRUD::field('primary_ad_rep_id')->type('number');
        CRUD::field('secondary_ad_rep_id')->type('number');

        CRUD::field('website_part')->type('textarea');
        CRUD::field('website')->type('textarea');
        CRUD::field('wisconsin_resale_number')->type('text');

        CRUD::field('owner_alumni_school_district')->type('select_from_array')->options([
            'gibraltar' => 'Gibraltar',
            'sevastopol' => 'Sevastopol',
            'southern_door' => 'Southern Door',
            'sturgeon_bay' => 'Sturgeon Bay',
            'washington_island' => 'Washington Island',
            'other' => 'Other',
            'non_dc' => 'Non Door County',
        ]);

        CRUD::field('newsletter_weekly_business_updates')->type('checkbox');
        CRUD::field('newsletter_pulse_picks')->type('checkbox');

        CRUD::field('marketing_preferences')->type('select_from_array')->options([
            'all_marketing' => 'All Marketing',
            'no_marketing' => 'No Marketing',
        ]);

        CRUD::field('marketing_contact_name');
        CRUD::field('marketing_contact_email_nickname');
        CRUD::field('marketing_contact_email')->type('email');
        CRUD::field('marketing_contact_phone_office');
        CRUD::field('marketing_contact_phone_mobile');

        CRUD::field('marketing_contact_preference')->type('select_from_array')->options([
            'email' => 'Email',
            'phone' => 'Phone',
            'text' => 'Text',
            'no_preference' => 'No Preference',
            'see_notes' => 'See Notes',
        ]);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
    public function bulkDelete(Request $request)
{
    $ids = $request->ids ?? [];

    if (!count($ids)) {
        return response()->json(['error' => 'No items selected'], 422);
    }

    Client::whereIn('id', $ids)->delete(); // Soft delete

    return response()->json(['success' => true]);
}
public function toggleActive(Request $request, $id)
{
    $client = Client::findOrFail($id);

    $client->active = $request->active ? 1 : 0;
    $client->save();

    return response()->json(['success' => true]);
}
public function duplicate(Request $request)
{
    $request->validate([
        'source_id' => 'required|exists:clients,id',
    ]);

    $source = Client::findOrFail($request->source_id);

    $new = $source->replicate();     // clone all attributes
    $new->name = $request->name;     // overridden values
    $new->phone = $request->phone;

    $new->save();                    // create new client

    return redirect(backpack_url('client'))
        ->with('success', 'Client duplicated successfully.');
}

}
