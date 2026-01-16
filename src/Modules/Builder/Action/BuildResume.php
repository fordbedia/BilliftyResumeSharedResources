<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Action;

use JustSteveKing\Resume\Builders\ResumeBuilder;
use JustSteveKing\Resume\DataObjects\Basics;
use JustSteveKing\Resume\DataObjects\Location;
use JustSteveKing\Resume\DataObjects\Profile;
use JustSteveKing\Resume\DataObjects\Work;
use JustSteveKing\Resume\Enums\Network;

final class BuildResume
{
    public function __invoke(): object
    {
        // Basics section (from package docs)
        $basics = new Basics(
            name: 'Ed Bedia',
            label: 'Full-Stack Software Engineer',
            email: 'fordbedia@gmail.com',
            url: 'https://fordbedia.com',
            summary: 'Full-stack engineer specializing in Laravel + React, with a strong testing mindset and a focus on clean architecture.',
            location: new Location(
                address: 'Houston, TX',
                postalCode: '',
                city: 'Houston',
                countryCode: 'US',
                region: 'TX',
            ),
            profiles: [
                new Profile(Network::GitHub, 'fordbedia', 'https://github.com/fordbedia'),
                new Profile(Network::LinkedIn, 'ed-bedia', 'https://linkedin.com/in/ed-bedia'),
            ],
        );

        // Build resume + add work experience (from package docs)
        $resume = (new ResumeBuilder())
            ->basics($basics)
            ->addWork(new Work(
                name: 'Freedom Forever LLC',
                position: 'Software Engineer',
                startDate: '2022-10-01',
                endDate: null,
                summary: 'Built and maintained internal platforms across Laravel + React.',
                highlights: [
                    'Shipped features across billing, invoicing, and operational workflows.',
                    'Improved reliability via tests and better separation of concerns.',
                ],
            ))
            ->build();

        return $resume;
    }
}
