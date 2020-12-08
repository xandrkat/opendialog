<?php

namespace App\Console\Commands\Specification;

use OpenDialogAi\ResponseEngine\OutgoingIntent;

class ExportIntents extends BaseSpecificationCommand
{
    protected $signature = 'intents:export {outgoingIntent?} {--y|yes}';

    protected $description = 'Export all outgoing intents';

    public function handle()
    {
        $outgoingIntentName = $this->argument('outgoingIntent');

        if ($this->option('yes')) {
            $continue = true;
        } elseif ($outgoingIntentName) {
            $continue = $this->confirm(
                sprintf(
                    'Do you want to export %s outgoing intent?',
                    $outgoingIntentName
                )
            );
        } else {
            $continue = $this->confirm('Do you want to export all outgoing intents?');
        }

        if ($continue) {
            if ($outgoingIntentName) {
                $outgoingIntent = OutgoingIntent::where('name', $outgoingIntentName)->first();
                $this->exportoutgoingIntent($outgoingIntent);
            } else {
                $outgoingIntents = OutgoingIntent::all();

                foreach ($outgoingIntents as $outgoingIntent) {
                    $this->exportoutgoingIntent($outgoingIntent);
                }
            }

            $this->info('Export of intents finished');
        } else {
            $this->info('Bye');
        }
    }

    protected function exportOutgoingIntent(OutgoingIntent $outgoingIntent): void
    {
        $this->info(sprintf('Exporting outgoing intent %s', $outgoingIntent->name));

        $output = "<intent>" . $outgoingIntent->name . "</intent>";

        $intentFileName = "$outgoingIntent->name.intent";
        $this->createIntentFile($intentFileName, $output);
    }
}