<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Database\Seeders;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Basic;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\ColorScheme;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Education;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Profile;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Reference;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Resume;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Skills;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Templates;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Work;
use BilliftyResumeSDK\SharedResources\SDK\Database\MakeSeeder;

class ResumeSeeder extends MakeSeeder
{
	protected array $resume = [
		'name' => 'Ed Bedia',
	];
	protected array $basic = [
		'name' => 'Edford Patrick Bedia',
		'label' => 'Software Engineer',
		'url' => 'http://fordbedia.com',
		'image' => null,
		'email' => 'fordbedia@gmail.com',
		'phone' => '8509386111',
		'website' => '',
		'address' => '1922 Whispering River Dr',
		'postalCode' => '77489',
		'city' => 'Missouri City',
		'countryCode' => 'USA',
		'region' => 'TX',
		'summary' => '<b>Full-stack software engineer </b>with <b>5+ years of experience</b> building and maintaining scalable, production-ready web applications. Strong background in <b>Laravel</b>, <b>PHP</b>, <b>React</b>, <b>Vue</b>, <b>Python</b> and working with <b>MySQL</b> and <b>SQL</b> <b>databases</b>, with a deep focus on clean architecture, testability, and maintainable code. Experienced in Agile environments, collaborating closely with product managers, designers, and stakeholders to deliver high-quality solutions. Known for being reliable, detail-oriented, and proactive in improving both codebase, team workflows and capable of writing advanced database queries to support complex business logic and reporting needs.',
	];
	protected array $work = [
		[
			'name' => 'Freedom Forever LLC',
			'position' => 'Full Stack Software Engineer',
			'startDate' => '2022-10',
			'endDate' => '2025-07',
			'summary' => '<ul><li>Collaborated with the team during sprint planning to address blockers, brainstorm solutions, and set goals for the next sprint.</li><li>Converted Figma designs into JSX React components while maintaining pixel-accurate look and feel.</li><li>Developed layered architecture (service layer for business logic, data access layer for queries and DB transactions).</li><li>Managed 2–3 Jira tickets daily, balancing priorities and completing work within sprint timelines.</li><li>Worked closely with stakeholders and product teams to clarify requirements and deliver strong user experiences.&nbsp;</li><li>Developed and maintained a Laravel module-based approach.</li><li>Designed, developed, and maintained database-driven scoped applications.</li><li>Wrote unit tests for every feature/logic implemented to prevent regressions and maintain high coverage.</li></ul>',
			'highlights' => '',
		],
		[
			'name' => 'CitronWorks, Inc.',
			'position' => 'Full Stack Software Developer',
			'startDate' => '2017-06',
			'endDate' => '2021-09',
			'summary' => '<ul><li>Converted mock-up designs into reusable VueJS components while preserving the original design intent.</li><li>Designed and implemented facade classes and layered architecture within Laravel modules.</li><li>Implemented a persistence layer to support domain aggregates.</li><li>Built Repository and Service layers to manage transactions and orchestration of business logic.</li><li>Wrote unit tests for core features to validate individual functions/methods.</li></ul>',
			'highlights' => '',
		],
		[
			'name' => 'Zenvoy, LLC.',
			'position' => 'Full Stack Software Developer',
			'startDate' => '2015-02',
			'endDate' => '2017-10',
			'summary' => '<ul><li>Convert PSDs into VueJS component ensuring the detailed original design.</li><li><span style="color: rgb(20, 24, 31);">Collaborated with the product team and stakeholders to deliver a good quality user experience&nbsp;</span></li><li><span style="color: rgb(20, 24, 31);">Wrote unit tests, integral tests and End-to-End UI test&nbsp;</span>to secure the durability of the system.</li><li>Participated in our regular developer\'s meeting with team members, leads and product teams to discuss daily and future tasks executions.</li><li>Embraced and maintained SOLID coding principles in all layers I wrote.</li></ul>',
			'highlights' => '',
		],
		[
			'name' => 'VTrix IT Solutions.',
			'position' => 'Full Stack Wordpress Web Developer',
			'startDate' => '2012-02',
			'endDate' => '2015-01',
			'summary' => '<ul><li>Developed custom WordPress themes and plugins&nbsp;<span style="color: rgb(20, 24, 31);">from the ground up, converting PSD designs into&nbsp;</span>reusable templates for pages and posts while maintaining a consistent look and feel.</li><li>Built fully responsive themes optimized for performance and cross-device compatibility.</li><li>Created flexible content management solutions using advanced Custom Fields (ACF) and Gutenberg, enabling non-technical users to manage content efficiently.</li><li>Developed a custom WordPress plugin that acted as a backend layer for the application, providing shared resources, abstractions, and a structured, layered architecture.</li></ul>',
			'highlights' => '',
		]
	];
	protected array $education = [
		[
			'institution' => 'Central Philippine University',
			'area' => 'Bachelor in Information Technology',
			'studyType' => 'College Degree',
			'startDate' => '2005-06',
			'endDate' => '2010-06',
		],
		[
			'institution' => 'Colegio del Sagrado Corazon de Jesus',
			'area' => 'High School',
			'studyType' => 'Secondary Level',
			'startDate' => '2001-06',
			'endDate' => '2005-03',
		]
	];
	protected array $skills = [
		'PHP','Laravel', 'Python', 'Django','React', 'Vue', 'NodeJS', 'Docker', 'Javascript', 'Typescript', 'ES6/ESNext',
		'HTML5','CSS', 'SCSS', 'SASS', 'JQuery', 'SOLID coding principles', 'DRY', 'Reusable and Readable', 'ORM libraries',
		'MVC Architecture', 'MVT Architecture', 'Problem-solving skills', 'Agile Planning', 'MySQL', 'PostgreSQL', 'WebSocket', 'SocketIO',
		'AWS', 'DigitalOcean'
	];
	protected array $profiles = [
		[
			'username' => 'edbedia',
			'url' => 'https://www.linkedin.com/in/ed-bedia/'
		]
	];
	protected array $references = [
		[
			'name' => 'Gerrit Bond',
			'reference' => 'Software Engineering Manager<br><b>Freedom Forever, LLC&nbsp;</b><br>Position: Manager&nbsp;<br>Email: <b>gerritbond@gmail.com&nbsp;</b><br>Phone: + <b>(804)305-0132</b><br>',
		]
	];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
		$templateId = Templates::query()->where('slug', 'basic')->first()->id;
		$colorSchemeId = ColorScheme::query()->where('slug', 'blue')->first()->id;
		$resume = Resume::query()->updateOrCreate($this->resume, $this->resume + [
				'user_id' => 1, 'template_id' => $templateId ?? '1',
				'color_scheme_id' => $colorSchemeId ?? '1'
			]);
		$basic = Basic::query()->updateOrCreate($this->basic + ['resume_id' => $resume->id]);

		foreach ($this->work as $work) {
			Work::query()->updateOrCreate($work + ['resume_id' => $resume->id]);
		}
		foreach ($this->skills as $i => $skill) {
			Skills::query()->updateOrCreate(['name' => $skill, 'resume_id' => $resume->id, 'sort_order' => $i]);
		}
		foreach ($this->profiles as $profile) {
			Profile::query()->updateOrCreate($profile + ['basic_id' => $basic->id]);
		}
		foreach ($this->education as $education) {
			Education::query()->updateOrCreate($education + ['resume_id' => $resume->id]);
		}
		foreach ($this->references as $reference) {
			Reference::query()->updateOrCreate($reference + ['resume_id' => $resume->id]);
		}
    }

    /**
     * Revert the database seeds.
     */
    public function revert(): void
    {
        //
    }
}
