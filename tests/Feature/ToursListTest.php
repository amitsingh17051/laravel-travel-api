<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\Travel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ToursListTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;


    public function test_tours_list_by_travel_slug_returns_correct_tours(): void
    {
        $travel = Travel::factory()->create();
        
        $tour = Tour::factory()->create(['travel_id' => $travel->id]);
      
        $respone = $this->get('api/v1/travels/'. $travel->slug. '/tours');
        //dd($respone);
        $respone->assertStatus(200);
        $respone->assertJsonCount(1, 'data');
        $respone->assertJsonFragment(['id' => $tour->id]);

    }

    public function test_tour_price_is_shown_corrently()  {
        $travel = Travel::factory()->create();

        Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 123.45
        ]);

        $respone = $this->get('api/v1/travels/'. $travel->slug. '/tours');

        $respone->assertStatus(200);
        $respone->assertJsonCount(1, 'data');
        $respone->assertJsonFragment(['price' => '123.45']);

    }

    public function test_tours_list_returns_pagination() {
        $toursPerPage = config('app.paginationPerPage.tours');
        $travel = Travel::factory()->create();
        
        Tour::factory($toursPerPage + 1)->create(['travel_id' => $travel->id]);

        $respone = $this->get('api/v1/travels/'. $travel->slug. '/tours');
        $respone->assertJsonCount(1, 'data');
        $respone->assertJsonPath('meta.current_page', 1);
    }

    public function test_tours_list_sorts_by_starting_date_correctly() 
    {
        $travel = Travel::factory()->create();
        $laterTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'starting_date' => now()->addDays(2),
            'ending_date' => now()->addDays(3),
        ]);

        $earliierTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'starting_date' => now(),
            'ending_date' => now()->addDays(1),
        ]);

        $respone = $this->get('api/v1/travels/'. $travel->slug. '/tours');
        $respone->assertStatus(200);
        //dd($respone);
        $respone->assertJsonPath('data.0.id', $earliierTour->id);
        $respone->assertJsonPath('data.1.id', $laterTour->id);
    }

    public function test_tours_list_sorts_by_price_correctly() 
    {
        $travel = Travel::factory()->create();
        $epensiveTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 200
        ]);
        $cheapLaterTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 100,
            'starting_date' => now()->addDays(2),
            'ending_date' => now()->addDays(3)
        ]);

        $respone = $this->get('api/v1/travels/'. $travel->slug. '/tours?sortBy=price&sortOrder=asc');
        $respone->assertStatus(200);
        $respone->assertJsonPath('data.0.id', $cheapLaterTour->id);
        $respone->assertJsonPath('data.1.id', $epensiveTour->id);
    }

    public function test_tours_list_filters_by_price_correctly()
    {
        $travel = Travel::factory()->create();
        $epensiveTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 200,
        ]);
        $cheapTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 100,
        ]);
       // dd($cheapTour);

        $endpoint = '/api/v1/travels/'.$travel->slug. '/tours';
        
        $respone = $this->get($endpoint. '?priceFrom=1');
        $respone->assertJsonCount(2, 'data');
        // $respone->assertJsonFragment(['id', $cheapTour->id]);
        //$respone->assertJsonFragment(['id', $epensiveTour->id]);
       
        // $respone = $this->get($endpoint. '?priceFrom=150');
        // $respone->assertJsonCount(1, 'data');
        // $respone->assertJsonMissing(['id', $cheapTour->id]);
        // $respone->assertJsonFragment(['id', $epensiveTour->id]);

        // $respone = $this->get($endpoint. '?priceFrom=250');
        // $respone->assertJsonCount(0, 'data');

        // $respone = $this->get($endpoint. '?priceFrom=200');
        // $respone->assertJsonCount(2, 'data');
        // $respone->assertJsonMissing(['id', $epensiveTour->id]);
        // $respone->assertJsonFragment(['id', $cheapTour->id]);

        // $respone = $this->get($endpoint. '?priceFrom=250');
        // $respone->assertJsonCount(0, 'data');
    }
}
