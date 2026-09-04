<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->unique()->sentence();

        return [
            'slug' => Str::limit(Str::slug($title, '-', 'nl'), 48, ''),
            'title' => $title,
            'intro' => $this->faker->realText(254),
            'content' => str('<h2>Hoelang nog, Catilina, zal je ons geduld misbruiken?</h2><p>Cicero, geboren in <a href="#">Italië</a>, een man van buitengewone welsprekendheid die zich onderscheidde door de veelzijdigheid van zijn kennis, was <em>het meest illustere voorbeeld van een Romeinse redenaar</em>. Zijn wijsheid, waarmee hij zowel openbare als privézaken leidde, bracht de staat vaak redding. Geroemd om zijn deugdzaamheid en standvastig in het vertrouwen op vriendschap, kende hij <strong>geen moment van ledigheid</strong>. Hij richtte zijn studies in literatuur en filosofie altijd op eer, en zijn woorden, vol ernst en charme, hebben het nageslacht verlichting gebracht.</p><blockquote><p>Wie wil het genot op zich nastreven,<br>omdat genot iets is dat men moet zoeken en nastreven;<br>maar omdat zulke momenten zich niet altijd voordoen,<br>zoekt men groot genot door middel van arbeid en pijn.</p></blockquote><h3>Hoelang nog zal die razernij van jou met ons de draak steken?</h3><ul><li><p>Cicero vertelt in zijn redevoeringen en werken veel over zichzelf.</li><li><p>Vrienden van hem, onder wie <a href="#">Titus</a> en <a href="#">Cornelius</a>, hebben over zijn leven geschreven.</p></li><li><p>Er is van Cicero een vrij omvangrijke briefwisseling bewaard gebleven.</p></li></ul><p>Het genot van de pijn nastreven. Nog zwaardere kwellingen. Het onderwerpen aan genot en zelfs aan de wil. Evenzo zijn de aanwezigen, verblind door haat, verblind door genot. Want hij, en zij, maken er gebruik van. Genot, dat het gemak verwerpt, is een onderscheid; het is datgene wat hij haat.</p><h4>Tot welk uiterste zal jouw lef zich grenzeloos roeren?</h4><ol><li><p>Cicero vertelt in zijn redevoeringen en werken veel over zichzelf.</li><li><p>Vrienden van hem, onder wie <a href="#">Titus</a> en <a href="#">Cornelius</a>, hebben over zijn leven geschreven.</p></li><li><p>Er is van Cicero een vrij omvangrijke briefwisseling bewaard gebleven.</p></li></ol>'),
            'published_at' => now(),
            'created_by_user_id' => 1,
            'updated_by_user_id' => 1,
        ];
    }

    /**
     * Indicate that the model is not published.
     */
    public function notPublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the model is soft-deleted.
     */
    public function softDeleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
        ]);
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Post $model) {
            $model->order_column = $model->id;
            $model->saveQuietly();
        });
    }
}
