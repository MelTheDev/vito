import { Head, useForm, usePage } from '@inertiajs/react';
import { Server } from '@/types/server';
import Container from '@/components/container';
import HeaderContainer from '@/components/header-container';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import ServerLayout from '@/layouts/server/layout';
import { CloudUploadIcon, LoaderCircleIcon } from 'lucide-react';
import { Backup } from '@/types/backup';
import { DataTable } from '@/components/data-table';
import { PaginatedData } from '@/types';
import { BackupFile } from '@/types/backup-file';
import { columns } from '@/pages/backups/components/file-columns';

type Page = {
  server: Server;
  backup: Backup;
  files: PaginatedData<BackupFile>;
};

export default function Files() {
  const page = usePage<Page>();

  const runBackupForm = useForm();
  const runBackup = () => {
    runBackupForm.post(route('backups.run', { server: page.props.server.id, backup: page.props.backup.id }));
  };

  return (
    <ServerLayout>
      <Head title={`Backup files - ${page.props.server.name}`} />

      <Container className="max-w-5xl">
        <HeaderContainer>
          <Heading title={`Backup files of ${page.props.backup.database.name}`} description="Here you can manage the backups of your database" />
          <div className="flex items-center gap-2">
            <Button onClick={runBackup}>
              {runBackupForm.processing ? <LoaderCircleIcon className="animate-spin" /> : <CloudUploadIcon />}
              <span className="hidden lg:block">Run backup</span>
            </Button>
          </div>
        </HeaderContainer>

        <DataTable columns={columns} paginatedData={page.props.files} />
      </Container>
    </ServerLayout>
  );
}
