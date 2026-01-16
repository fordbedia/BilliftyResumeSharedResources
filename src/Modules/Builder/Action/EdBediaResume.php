<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Action;

use JustSteveKing\Resume\Builders\ResumeBuilder;
use JustSteveKing\Resume\DataObjects\Basics;
use JustSteveKing\Resume\DataObjects\Education;
use JustSteveKing\Resume\DataObjects\Location;
use JustSteveKing\Resume\DataObjects\Profile;
use JustSteveKing\Resume\DataObjects\Reference;
use JustSteveKing\Resume\DataObjects\Skill;
use JustSteveKing\Resume\DataObjects\Work;
use JustSteveKing\Resume\Enums\EducationLevel;
use JustSteveKing\Resume\Enums\Network;

final class EdBediaResume
{
	public function __invoke()
	{
		return $this->build();
	}

	public function build(): object
    {
        $basics = new Basics(
            name: 'Edford Patrick Bedia',
            label: 'Full-stack Software Engineer',
            email: 'fordbedia@gmail.com',
            phone: '8509386111',
            url: 'https://fordbedia.com/',
            summary: 'Full-stack software engineer with 5+ years of experience building and maintaining scalable, production-ready web applications. Strong background in Laravel, PHP, React and Vue, with a deep focus on clean architecture, testability, and maintainable code. Experienced in Agile environments, collaborating closely with product managers, designers, and stakeholders to deliver high-quality solutions. Known for being reliable, detail-oriented, and proactive in improving both codebase and team workflows.',
            location: new Location(
                address: 'Missouri City, Texas 77489',
                postalCode: '77489',
                city: 'Missouri City',
                countryCode: 'US',
                region: 'TX',
            ),
            profiles: [
                new Profile(
                    network: Network::LinkedIn,
                    username: 'ed-bedia',
                    url: 'https://www.linkedin.com/in/ed-bedia/'
                ),
                new Profile(
                    network: Network::Other,
                    username: 'fordbedia.com',
                    url: 'https://fordbedia.com/'
                ),
            ],
        );

        $builder = (new ResumeBuilder())
            ->basics($basics);

        // --- Skills (from resume)
        foreach ([
            'PHP',
            'Laravel',
            'React',
            'Vue',
            'NodeJS',
            'Docker',
            'JavaScript',
            'TypeScript',
            'ES6/ESNext',
            'HTML5',
            'CSS',
            'SCSS',
            'SASS',
            'jQuery',
            'SOLID coding principles',
            'DRY',
            'Reusable and Readable',
            'ORM libraries',
            'MVC Architecture',
            'MVT Architecture',
            'Problem-solving skills',
            'Agile Planning',
            'MySQL',
            'PostgreSQL',
            'WebSocket',
            'SocketIO',
            'AWS',
            'DigitalOcean',
        ] as $skillName) {
            $builder->addSkill(new Skill(name: $skillName));
        }

        // --- Work History
        $builder->addWork(new Work(
            name: 'Freedom Forever LLC.',
            position: 'Full-Stack Software Engineer',
            startDate: '2022-10-01',
            endDate: '2025-07-01',
            summary: null,
            highlights: [
                'Collaborated with the team during sprint planning to address blockers, brainstorm solutions, and set goals for the next sprint.',
                'Converted Figma designs into JSX React components while maintaining pixel-accurate look and feel.',
                'Developed layered architecture (service layer for business logic, data access layer for queries and DB transactions).',
                'Managed 2–3 Jira tickets daily, balancing priorities and completing work within sprint timelines.',
                'Worked closely with stakeholders and product teams to clarify requirements and deliver strong user experiences.',
                'Developed and maintained a Laravel module-based approach.',
                'Designed, developed, and maintained database-driven scoped applications.',
                'Wrote unit tests for every feature/logic implemented to prevent regressions and maintain high coverage.',
            ],
        ));

        $builder->addWork(new Work(
            name: 'CitronWorks, Inc.',
            position: 'Full Stack Software Developer',
            startDate: '2017-06-01',
            endDate: '2021-09-01',
            summary: null,
            highlights: [
                'Converted mock-up designs into reusable VueJS components while preserving the original design intent.',
                'Designed and implemented facade classes and layered architecture within Laravel modules.',
                'Implemented a persistence layer to support domain aggregates.',
                'Built Repository and Service layers to manage transactions and orchestration of business logic.',
                'Wrote unit tests for core features to validate individual functions/methods.',
            ],
        ));

        $builder->addWork(new Work(
            name: 'Zenvoy, LLC.',
            position: 'Full Stack Software Developer',
            startDate: '2015-02-01',
            endDate: '2017-10-01',
            summary: null,
            highlights: [
                'Converted PSD designs into VueJS components while ensuring detailed fidelity to original designs.',
                'Collaborated with product teams and stakeholders to deliver quality user experiences.',
                'Wrote unit tests, integration tests, and end-to-end UI tests to improve system durability.',
                'Participated in regular dev meetings to align on daily and upcoming tasks.',
                'Embraced SOLID principles across implemented layers.',
            ],
        ));

        $builder->addWork(new Work(
            name: 'VTrix IT Solutions.',
            position: 'Full Stack WordPress Web Developer',
            startDate: '2012-02-01',
            endDate: '2015-01-01',
            summary: null,
            highlights: [
                'Developed custom WordPress themes and plugins from the ground up, converting PSDs into reusable templates for pages and posts.',
                'Built responsive themes optimized for performance and cross-device compatibility.',
                'Created flexible CMS solutions using Advanced Custom Fields (ACF) and Gutenberg.',
                'Developed a custom WordPress plugin that acted as a backend layer, providing shared resources, abstractions, and a structured layered architecture.',
            ],
        ));

        // --- Education
        $builder->addEducation(new Education(
            institution: 'Central Philippine University',
            area: 'Information Technology',
            studyType: EducationLevel::Bachelor,
            startDate: null,
            endDate: '2010-06-01',
            score: null,
            courses: [],
        ));

        $builder->addEducation(new Education(
            institution: 'Colegio Del Sagrado',
            area: 'Secondary Level',
            studyType: null,
            startDate: null,
            endDate: '2005-01-01',
            score: null,
            courses: [],
        ));

        // --- References
        $builder->addReference(new Reference(
            name: 'Gerrit Bond',
            reference: 'Software Engineering Manager, Freedom Forever, LLC — Manager. Email: gerritbond@gmail.com | Phone: +1 (804) 305-0132'
        ));

        return $builder->build();
    }
}
